<?php

namespace App\Controllers;


class HomeController extends BaseController
{
    public function index()
    {

        return view('home', ['title' => 'Home Page']);
    }
}