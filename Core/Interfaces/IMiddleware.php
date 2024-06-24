<?php

namespace Core\Interfaces;

use Core\Request;
use Core\Response;

/**
 * Middleware Interface
 */
interface IMiddleware
{

    /**
     * Do whatever processing this middleware needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * Core\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     */
    public function before(Request $request, Response $response);



    /**
     * Allows After middleware to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     */
    public function after(Request $request, Response $response);

}