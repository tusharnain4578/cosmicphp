<?php

namespace Core;
use \Exception;


class ExceptionHandler
{
    private string $environment;

    public function __construct()
    {
        $this->environment = env('ENVIRONMENT', 'production');
    }
    private function isProduction() : bool
    {
        return $this->environment === 'production';
    }
    private function isDevelopment() : bool
    {
        return $this->environment === 'development';
    }
    public function handle(Exception $e)
    {
        if($this->isProduction())
        {
            response()->setStatusCode(statusCode: 500)->send((new View)->core()->render('errors.error_exception'));
        }else
        {
            throw $e;
        }   
    }
}