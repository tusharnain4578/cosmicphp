<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;


class DevController extends Controller
{


    public function index(Request $request)
    {

        throw new \Exception("example exception");

        return ['success' => true];
    }
}