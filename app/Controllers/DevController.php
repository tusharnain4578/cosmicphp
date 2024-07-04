<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;
use App\Models\Wallet;


class DevController extends Controller
{

    
    public function index()
    {



        return ['success'=> true, 'message'=> 'Dev'];
    }
}