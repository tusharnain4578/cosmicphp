<?php

namespace App\Middlewares;

use Core\Interfaces\IMiddleware;
use Core\Request;
use Core\Response;

class TestMiddleware implements IMiddleware
{

    /**
     * if 'before' and 'after' methods do not return anything, execution will react to the controller or next middleware.
     * 
     * They should only return an instance of Core\Response class.
     * 
     * If Response is returned, then that response is sent to the client immediately
     * 
     * And Redirect Response is also going to take action immediately
     */

    public function before(Request $request, Response $response)
    {
        d('Test Middleware');
    }
    public function after(Request $request, Response $response)
    {

    }
}