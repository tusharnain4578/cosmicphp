<?php

namespace Core\Base;

use Core\Request;
use Core\Response;

abstract class Controller
{
    protected ?Request $request = null;
    protected ?Response $response = null;

    protected function render(string $view, array $data = []): string
    {
        return view($view, $data);
    }

    public function initController()
    {
        $this->request = request();
        $this->response = response();
    }
}