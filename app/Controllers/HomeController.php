<?php

namespace App\Controllers;

use Core\Controller;


class HomeController extends Controller
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