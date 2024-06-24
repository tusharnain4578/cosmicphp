<?php

namespace Core;


use App\Config\middleware as middlewareConfig;
use Core\Interfaces\IMiddleware;
use Core\Utilities\File;
use Core\Utilities\Path;
use App\Config\router as routerConfig;

class Router
{
    private Request $request;
    private Response $response;
    private array $routes = [];
    private ?string $requestMethod;
    private string $startingScriptUrl;
    private string $requestPath;
    private string $appRoutesDirectory = 'Routes';
    private string $controllerNamespace = "App\Controllers";
    private array $namedRoutes = []; // name(string) as key and routeIndex(int) as value
    private int $routesCount = 0;
    private array $currentGroup = []; // prefix(string) as key and options(array) as value
    private string $ROUTE_GROUP_NAME_SEPARATOR = '.';

    public function __construct()
    {
        $this->request = new Request;
        $this->response = new Response;

        $this->requestMethod = $this->request->method();
        $this->startingScriptUrl = $this->request->startingScriptUrl();
        $this->requestPath = $this->request->getUri();

        if (defined(routerConfig::class . "::ROUTE_GROUP_NAME_SEPARATOR"))
            $this->ROUTE_GROUP_NAME_SEPARATOR = routerConfig::ROUTE_GROUP_NAME_SEPARATOR;

    }

    public function get(string $path, $handler, array $routeOptions = []): self
    {
        $this->addHandler(Request::METHOD_GET, $path, $handler, $routeOptions);
        return $this;
    }

    public function post(string $path, $handler): self
    {
        $this->addHandler(Request::METHOD_POST, $path, $handler);
        return $this;
    }

    public function group(string $prefix, callable|array $funcOrOptions, ?callable $func = null)
    {
        $prefix = trim($prefix, '\/\ ');
        $groupData = [];
        if (is_array($funcOrOptions) && !empty($funcOrOptions))
            $groupData = $funcOrOptions;
        $groupData['prefix'] = $prefix;
        $this->currentGroup[] = $groupData; // starting group
        if (is_callable($funcOrOptions))
            $funcOrOptions($this);
        elseif ($func)
            $func($this);
        else
            throw new \Exception("Callable method not provided for route grouping.");
        array_pop($this->currentGroup); // ending group
    }

    public function name(string $name): self
    {
        if ($this->routesCount > 0) {
            $name = trim($name);
            if (empty($name))
                throw new \Exception("Route name cannot be empty!");

            // merging with group name
            $name = (function () use ($name): string{
                if (empty($this->currentGroup) || empty($groupNames = array_column($this->currentGroup, 'name')))
                    return $name;
                $groupNamesString = implode($this->ROUTE_GROUP_NAME_SEPARATOR, $groupNames) . (!empty($name) ? $this->ROUTE_GROUP_NAME_SEPARATOR : '');
                return $groupNamesString . $name;
            })();


            $this->namedRoutes[$name] = $this->routesCount - 1;
        }
        return $this;
    }


    public function middleware(string|array $middleware): self
    {
        $index = $this->routesCount - 1;
        if (($this->routesCount > 0) && isset($this->routes[$index])) {
            if (is_string($middleware))
                $middleware = [$middleware];
            foreach ($middleware as &$mw) {
                $mw = trim($mw);
                if (empty($mw))
                    throw new \Exception("Middleware cannot be empty!");
                $this->routes[$index]['middlewares'][] = $mw;
            }
        }
        return $this;
    }




    /**
     * This method is for named routes
     */
    public function route(string $name): string
    {
        $routeIndex = $this->namedRoutes[$name] ?? null;
        if (is_null($routeIndex))
            throw new \Exception("Route with name $name not found!");
        $route = $this->routes[$routeIndex];
        return base_url($route['path']);
    }

    public function init()
    {
        // will scan the application Router Directory to initalize router from all files
        $appRoutesPath = Path::appPath('Routes');
        $routeFiles = File::scan_directory($appRoutesPath);
        foreach ($routeFiles as $file) {
            (function () use ($appRoutesPath, $file) {
                $router = $this;
                require_once $appRoutesPath . DIRECTORY_SEPARATOR . $file;
            })();
        }
        $this->run();
    }



    private function run(): void
    {
        $targetRoute = null;
        foreach ($this->routes as $route) {
            $routePath = $route['path'];
            if ($routePath === $this->requestPath && $route['method'] === $this->requestMethod) {
                $targetRoute = $route;
                break;
            }
        }

        if ($targetRoute) {

            $this->executeRoute($targetRoute);

        } else {
            echo "NOT FOUND";
        }
    }


    private function addHandler(string $method, string $path, $handler, array $routeOptions = []): void
    {
        $path = trim($path, '\/\ ');

        if (!empty($this->currentGroup)) {
            // applying group prefixes to route
            $path = (function () use ($path): string{
                $prefixes = array_column($this->currentGroup, 'prefix');
                $group = '';
                foreach ($prefixes as &$prefix)
                    $group .= !empty($prefix) ? $prefix . '/' : '';
                if (empty($path))
                    $group = rtrim($group, '\/');
                return $group . $path;
            })();
        }

        $this->routes[] = [
            'path' => $path,
            'method' => $method,
            'handler' => $handler,
            'middlewares' => []
        ];

        $this->routesCount++; // placed right after insertion of routes array



        if (!empty($this->currentGroup)) {

            // applying group middlewares
            $groupMiddlewares = array_column($this->currentGroup, 'middleware');
            if (!empty($groupMiddlewares))
                $this->middleware(middleware: $groupMiddlewares);

        }


        // applying middlewares
        if (isset($routeOptions['middleware']))
            $this->middleware($routeOptions['middleware']);

        if (isset($routeOptions['name']))
            $this->name($routeOptions['name']);

    }

    private function getControllerFromString(string $callback): array
    {
        $parts = explode(separator: '::', string: $callback);
        $className = $parts[0];
        $actionMethod = $parts[1];
        $fullClassName = "\\{$this->controllerNamespace}\\{$className}";
        return [$fullClassName, $actionMethod]; // [class, method] -> destructurable
    }



    private function executeRoute(array $route)
    {
        $middlewares = array_map(function (string $middleware) {
            if (!class_exists($middleware))
                $middlewareObj = middlewareConfig::getMiddlewareFromAlias(alias: $middleware);
            if (!($middlewareObj instanceof IMiddleware))
                throw new \Exception("Middleware : $middleware is not a valid middleware class.");
            return $middlewareObj;
        }, $route['middlewares']);

        // running middleware -> before
        $this->executeMiddlewares($middlewares, type: 'before'); // this can stop the script, if some response is returned from any middleware

        // running controller
        $callback = $route['handler'];
        if (is_string($callback)) {
            [$class, $method] = $this->getControllerFromString($callback);
            $controller = new $class;
            $controller->initController();
            $callback = [$controller, $method];
        }
        $controllerResult = call_user_func($callback);
        $this->response->setResponseBody(data: $controllerResult); // setting the controller return into response

        // running middleware -> after
        $this->executeMiddlewares($middlewares, type: 'after'); // this can stop the script, if some response is returned from any middleware

        $this->response->sendAndExit();
    }
    private function executeMiddlewares(array $middlewares, string $type)
    {
        if (!in_array($type, ['before', 'after']))
            throw new \Exception("$type is not a middleware type");
        foreach ($middlewares as $middleware) {
            $returnResponse = $middleware->$type(request: $this->request, response: $this->response);
            if ($returnResponse instanceof Response)
                $returnResponse->sendAndExit(); // script will end here
        }
    }
}