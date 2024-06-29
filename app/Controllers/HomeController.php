<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;


class HomeController extends Controller
{
    public function resume()
    {
        return view('resume');
    }
    public function projects()
    {
        if ($this->request->isPost())
            return ['success' => true];

        return view('projects');
    }
    public function contact()
    {
        return view('contact');
    }
    public function index(Request $request)
    {
        return view('home');
    }


    public function json($name)
    {
        return [
            'success' => true,
            'name' => $name
        ];
    }


}