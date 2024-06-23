<?php

namespace Core;

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

    public function get(string $path, $handler): self
    {
        $this->addHandler(method: Request::METHOD_GET, path: $path, handler: $handler);
        return $this;
    }

    public function post(string $path, $handler): self
    {
        $this->addHandler(method: Request::METHOD_POST, path: $path, handler: $handler);
        return $this;
    }

    public function group(string $prefix, callable|array $funcOrOptions, ?callable $func = null)
    {
        $prefix = trim($prefix, '\/\ ');

        if (empty($prefix))
            throw new \Exception("Route group prefix can't be empty!");
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
            $groupNames = array_column($this->currentGroup, 'name');
            if (!empty($groupNames)) {
                $groupNamesString = implode($this->ROUTE_GROUP_NAME_SEPARATOR, $groupNames) . (!empty($name) ? $this->ROUTE_GROUP_NAME_SEPARATOR : '');
                $name = $groupNamesString . $name;
            }

            $this->namedRoutes[$name] = $this->routesCount - 1;
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
        $router = $this;
        foreach ($routeFiles as $file)
            require_once $appRoutesPath . DIRECTORY_SEPARATOR . $file;
        $this->run();
    }



    private function run(): void
    {
        $callback = null;
        foreach ($this->routes as $route) {
            $routePath = $route['path'];
            if ($routePath === $this->requestPath && $route['method'] === $this->requestMethod) {
                $callback = $route['handler'];
            }
        }

        if ($callback) {
            $handlerReturnedData = $this->runHandler($callback);
            $this->response->send(data: $handlerReturnedData);
        } else {
            echo "NOT FOUND";
        }
    }


    private function addHandler(string $method, string $path, $handler): void
    {
        $path = trim($path, '\/\ ');

        $group = '';
        if (!empty($this->currentGroup)) {
            $prefixes = array_column($this->currentGroup, 'prefix');
            $group = implode('/', $prefixes) . (!empty($path) ? '/' : '');
        }
        $this->routes[] = [
            'path' => $group . $path,
            'method' => $method,
            'handler' => $handler,
        ];
        $this->routesCount++;
    }

    private function getControllerFromString(string $callback): array
    {
        $parts = explode(separator: '::', string: $callback);
        $className = $parts[0];
        $actionMethod = $parts[1];
        $fullClassName = "\\{$this->controllerNamespace}\\{$className}";
        return ['class' => $fullClassName, 'method' => $actionMethod];
    }
    private function runHandler($callback)
    {
        if (is_string($callback)) {

            $controllerParts = $this->getControllerFromString($callback);

            $controller = new $controllerParts['class'];

            // running initController
            $controller->initController();

            $contollerMethod = $controllerParts['method'];

            $callback = [$controller, $contollerMethod];
        }

        $controllerResult = call_user_func($callback);

        return $controllerResult;
    }
}