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
        return view('projects');
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