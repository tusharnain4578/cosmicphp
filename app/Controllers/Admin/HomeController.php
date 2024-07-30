<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\Models\Wallet;
use Core\Controller;


class HomeController extends Controller
{


    public function index()
    {

        $user = User::find(1);


        dd($user->wallet());


        return [];

    }
}