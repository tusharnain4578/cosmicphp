<?php

namespace App\Controllers;


use Core\Base\Controller;
use Core\Request;


class HomeController extends Controller
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
    public function index(Request $request)
    {
        return $this->render('home', ['name' => 'Tushar']);
    }

    public function login(Request $request)
    {


        return $this->render('login');
    }

}
