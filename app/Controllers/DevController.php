<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;
use App\Models\Wallet;


class DevController extends Controller
{


    public function index()
    {

        $data = User::all();

        $user = User::find(2);



        dd($user->posts);





        // to array
        $attributes = [];
        foreach ($data as $dt)
            $attributes[] = $dt->toArray();

        return ['success' => true, 'message' => $attributes];
    }
}