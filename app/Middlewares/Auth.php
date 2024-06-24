<?php

namespace App\Middlewares;

use Core\Interfaces\IMiddleware;
use Core\Request;
use Core\Response;

class Auth implements IMiddleware
{
    public function before(Request $request, Response $response)
    {
        echo "Auth Middleware";
    }
    public function after(Request $request, Response $response)
    {

    }
}