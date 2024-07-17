<?php

namespace Core;


use App\Config\middleware as middlewareConfig;
use Core\Interfaces\IMiddleware;
use Core\Utilities\File;
use Core\Utilities\Path;
use Core\Utilities\Arr;
use App\Config\UtilityConfig;

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
    private array $placeholders = [
        'any' => '.*',
        'segment' => '[^/]+',
        'alpha' => '[a-zA-Z]+',
        'num' => '[0-9]+',
        'alpha_num' => '[a-zA-Z0-9]+',
        'alpha_spaces' => '[a-zA-Z ]+',
        'alpha_num_spaces' => '[a-zA-Z0-9 ]+',
        'hash' => '[^/]+',
        'slug' => '[a-zA-Z0-9_-]+',
    ];
    private string $ROUTE_GROUP_NAME_SEPARATOR = '.';

    private const ROUTE_HANDLER_STRING_SEPARATOR = '@';
    private const ROUTE_CACHE_PHP_FILE_NAME = 'core_routes.php';

    public function __construct()
    {
        $this->request = request();
        $this->response = response();

        $this->requestMethod = $this->request->method();
        $this->startingScriptUrl = $this->request->startingScriptUrl();
        $this->requestPath = $this->request->getUri();

        if (defined(UtilityConfig::class . "::ROUTE_GROUP_NAME_SEPARATOR"))
            $this->ROUTE_GROUP_NAME_SEPARATOR = UtilityConfig::ROUTE_GROUP_NAME_SEPARATOR;

    }

    public function get(string $path, string|array $handler, array $routeOptions = []): self
    {
        $this->addHandler(Request::METHOD_GET, $path, $handler, $routeOptions);
        return $this;
    }

    public function post(string $path, string|array $handler, array $routeOptions = []): self
    {
        $this->addHandler(Request::METHOD_POST, $path, $handler, $routeOptions);
        return $this;
    }

    public function match(array $methods, string $path, string|array $handler, array $routeOptions = []): self
    {
        if (empty($methods))
            throw new \Exception("Methods array cannot be empty!");
        $methods = array_map(function (string $method): string {
            $method = strtoupper(trim($method));
            if (empty($method))
                throw new \Exception("Method can't be empty!");
            if (!in_array($method, Request::METHODS))
                throw new \Exception("Invalid method : '$method' provided");
            return $method;
        }, $methods);

        $methods = array_map('trim', $methods);
        $this->addHandler($methods, $path, $handler, $routeOptions);
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

    public function withoutMiddleware(string|array $middleware): self
    {
        $routeIndex = $this->routesCount - 1;
        if (($this->routesCount > 0) && isset($this->routes[$routeIndex])) {
            if (is_string($middleware))
                $middleware = [$middleware];
            foreach ($middleware as &$mw) {
                $mw = trim($mw);
                if (empty($mw))
                    throw new \Exception("Middleware cannot be empty!");
                // remove the middleware
                foreach ($this->routes[$routeIndex]['middlewares'] as $index => &$routeMw) {
                    if ($routeMw === $mw)
                        unset($this->routes[$routeIndex]['middlewares'][$index]);
                }

            }
        }
        return $this;
    }


    /**
     * This method is for named routes
     */
    public function route(string $name, ...$params): string
    {
        $routeIndex = $this->namedRoutes[$name] ?? null;
        if (is_null($routeIndex))
            throw new \Exception("Route with name '$name' not found!");
        $route = $this->routes[$routeIndex];
        $path = $route['path'];
        // checking for parameters
        preg_match_all('/\(([^)]+)\)/', $path, $matches);
        $placeholders = $matches[0];
        foreach ($placeholders as $index => $placeholder) {
            if (!isset($params[$index])) {
                throw new \InvalidArgumentException('Missing argument for "' . $placeholder . '" in route "' . $path . '".');
            }
            // placeholder is our regex
            if (!preg_match('#^' . $placeholder . '$#u', $params[$index]))
                throw new \Exception("Invalid parameter type!");
            // the expected param type.
            $pos = strpos($path, $placeholder);
            $path = substr_replace($path, $params[$index], $pos, strlen($placeholder));
        }
        return base_url($path);
    }

    public function init()
    {

        $cachedRouteData = cache()->getPHPFileCache(self::ROUTE_CACHE_PHP_FILE_NAME);

        if ($cachedRouteData && is_array($cachedRouteData) && !empty($cachedRouteData)) {
            $this->routes = $cachedRouteData['routes'] ?? [];
            $this->namedRoutes = $cachedRouteData['named_routes'] ?? [];
            $this->routesCount = $cachedRouteData['count'] ?? 0;
        } else {
            // will scan the application Router Directory to initalize router from all files
            // and populate the $this->routes array
            $appRoutesPath = Path::appPath($this->appRoutesDirectory);
            $routeFiles = File::scan_directory($appRoutesPath);
            foreach ($routeFiles as $file) {
                (function () use ($appRoutesPath, $file) {
                    $router = $this;
                    require_once $appRoutesPath . DIRECTORY_SEPARATOR . $file;
                })();
            }
            // caching routes
            if (!empty($this->routes)) {
                $cacheData = ['routes' => $this->routes, 'named_routes' => $this->namedRoutes, 'count' => $this->routesCount];
                $phpFileContent = Arr::array_to_php_return_file_string($cacheData, minimized: true);
                cache()->setPHPFileCache(filename: self::ROUTE_CACHE_PHP_FILE_NAME, content: $phpFileContent);
            }
        }

        $this->run();
    }



    private function run(): void
    {
        $targetRoute = null;
        $params = [];
        foreach ($this->routes as $route) {
            if (
                preg_match('#^' . $route['path'] . '$#u', $this->requestPath, $matches)
                && in_array($this->requestMethod, $route['methods'])
            ) {
                $targetRoute = $route;
                array_shift($matches);
                $params = $matches;
                break;
            }
        }
        if ($targetRoute) {
            $this->executeRoute($targetRoute, $params);
        } else {
            $this->response->setStatusCode(statusCode: 404)->send((new View)->core()->render('errors.error_404'));
        }
    }


    private function addHandler(string|array $methods, string $path, array|string $handler, array $routeOptions = []): void
    {
        $path = trim($path, '\/\ ');

        // adding placeholder in route with the corresponding regex
        foreach ($this->placeholders as $tag => $pattern)
            $path = str_ireplace(":$tag)", "$pattern)", $path);

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

        if (is_string($methods))
            $methods = [$methods];

        $this->routes[] = [
            'path' => $path,
            'methods' => $methods,
            'handler' => is_string($handler) ? $this->getControllerFromString($handler) : $handler,
            'middlewares' => []
        ];

        $this->routesCount++; // placed right after insertion of routes array

        if (!empty($this->currentGroup)) {
            // applying and removing group middlewares
            foreach ($this->currentGroup as $group) {
                $groupMiddlewares = $group['middleware'] ?? [];
                $groupWithoutMiddlewares = $group['withoutMiddleware'] ?? [];
                $this->middleware(middleware: $groupMiddlewares);
                $this->withoutMiddleware(middleware: $groupWithoutMiddlewares);
            }
        }


        // applying middlewares
        if (isset($routeOptions['middleware']))
            $this->middleware($routeOptions['middleware']);

        // removing middlewares
        if (isset($routeOptions['withoutMiddleware']))
            $this->withoutMiddleware($routeOptions['withoutMiddleware']);

        if (isset($routeOptions['name']))
            $this->name($routeOptions['name']);

    }

    private function getControllerFromString(string $callback): array
    {
        $parts = explode(separator: self::ROUTE_HANDLER_STRING_SEPARATOR, string: $callback);
        $className = $parts[0];
        $actionMethod = $parts[1];
        $fullClassName = "\\{$this->controllerNamespace}\\{$className}";
        return [$fullClassName, $actionMethod]; // [class, method] -> destructurable
    }



    private function executeRoute(array $route, array $params = [])
    {
        $middlewares = array_map(function (string $middleware) {
            $middlewareObj = class_exists($middleware) ? new $middleware() : middlewareConfig::getMiddlewareFromAlias(alias: $middleware);
            if (!($middlewareObj instanceof IMiddleware))
                throw new \Exception("Middleware : $middleware is not a valid middleware class.");
            return $middlewareObj;
        }, $route['middlewares']);

        // running middleware -> before
        $this->executeMiddlewares($middlewares, type: 'before'); // this can stop the script, if some response is returned from any middleware

        // running controller
        [$class, $method] = $route['handler'];
        // $this->getControllerFromString($callback)

        $controller = new $class;
        $controller->initController();
        $callback = [$controller, $method]; // [object, method]

        // first route parameters will be injected, after then Request and Response Object.
        $params = array_merge($params, [$this->request, $this->response]);
        $controllerResult = call_user_func_array($callback, $params);

        $this->response->setResponseBody(data: $controllerResult); // setting the controller return into response

        // running middleware -> after
        $this->executeMiddlewares($middlewares, type: 'after'); // this can stop the script, if some response is returned from any middleware

        $this->response->send();
    }
    private function executeMiddlewares(array $middlewares, string $type)
    {
        if (!in_array($type, ['before', 'after']))
            throw new \Exception("$type is not a middleware type");
        foreach ($middlewares as $middleware) {
            $returnResponse = $middleware->$type(request: $this->request, response: $this->response);
            if ($returnResponse instanceof Response)
                $returnResponse->send(); // script will end here
        }
    }
}