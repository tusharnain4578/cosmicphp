<?php

namespace App\Controllers;


use Core\Controller;
use Core\Request;


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

        if ($this->request->isPost())
            return $this->request->inputPost();


        return view('contact');
    }
    public function index(Request $request)
    {




        return $this->render('home', ['name' => 'Tushar']);
    }

    public function json($name)
    {
        return [
            'success' => true,
            'name' => $name
        ];
    }


}
