<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;


class DevController extends Controller
{


    public function index(Request $request)
    {



        return ['success' => true];
    }
}