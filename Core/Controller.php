<?php

namespace Core;

abstract class Controller
{
    protected ?Request $request = null;
    protected ?Response $response = null;

    public function initController()
    {
        $this->request = request();
        $this->response = response();
    }
}