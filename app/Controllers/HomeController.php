<?php

namespace App\Controllers;


class HomeController extends BaseController
{
    public function resume()
    {
        return view('resume');
    }
    public function projects()
    {
        return $this->response->view('projects');
    }
    public function contact()
    {
        return view('contact');
    }
    public function index()
    {
        return view('home');
    }
}