<?php

namespace Framework;

abstract class Controller
{
    protected ?Request $request = null;
    protected ?Response $response = null;

    public function initController()
    {
        $this->request = new Request;
        $this->response = new Response;
    }
}