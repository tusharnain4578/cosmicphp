<?php

namespace Core\Middlewares;


use Core\Exceptions\SecurityException;
use Core\Interfaces\IMiddleware;
use Core\Request;
use Core\Response;
use Core\Security\Csrf as CsrfSecurity;

class Csrf implements IMiddleware
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
        $csrfSecurity = new CsrfSecurity();

        if (!$csrfSecurity->verifyToken()) {

            throw SecurityException::actionNotAllowed();

        }
    }
    public function after(Request $request, Response $response)
    {

    }
}