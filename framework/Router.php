<?php

namespace Framework;

class Router
{
    private Request $request;
    private Response $response;
    private array $handlers = [];
    private ?string $requestMethod;
    private string $startingScriptUrl;
    private string $requestPath;
    private string $appRoutesDirectory = 'Routes';
    private string $controllerNamespace = "App\Controllers";
    private const METHOD_GET = 'GET';
    private const METHOD_POST = 'POST';

    public function __construct()
    {
        $this->request = new Request;
        $this->response = new Response;

        $this->requestMethod = $this->request->method();
        $this->startingScriptUrl = $this->request->startingScriptUrl();
        $this->requestPath = $this->request->getUri();
    }

    public function get(string $path, $handler): self
    {
        $this->addHandler(method: self::METHOD_GET, path: $path, handler: $handler);
        return $this;
    }

    public function post(string $path, $handler): self
    {
        $this->addHandler(method: self::METHOD_POST, path: $path, handler: $handler);
        return $this;
    }


    public function init()
    {
        // will scan the application Router Directory to initalize router from all files
        $appRoutesPath = Path::appPath('Routes');
        $routeFiles = array_diff(scandir($appRoutesPath), ['.', '..']);
        $router = $this;
        foreach ($routeFiles as $file)
            require_once $appRoutesPath . DIRECTORY_SEPARATOR . $file;
        $this->run();
    }



    private function run(): void
    {
        $callback = null;
        foreach ($this->handlers as $handler) {
            $handlerPath = trim($handler['path'], '\/\ ');
            if ($handlerPath === $this->requestPath && $handler['method'] === $this->requestMethod) {
                $callback = $handler['handler'];
            }
        }

        if (!$callback) {
            echo "NOT FOUND";
            exit; //good bye
        }

        $handlerReturnedData = $this->runHandler($callback);
        $this->response->send(data: $handlerReturnedData);
    }


    private function addHandler(string $method, string $path, $handler): void
    {
        $this->handlers[$method . $path] = [
            'path' => $path,
            'method' => $method,
            'handler' => $handler
        ];
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