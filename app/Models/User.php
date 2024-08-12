<?php

namespace App\Models;


use Core\Services\Authentication\User as Authenticable;

class User extends Authenticable
{
    protected static string $table = 'users';
    protected static array $fillable = ['full_name', 'email', 'phone', 'role', 'password'];
    protected static string $createdField = 'created_at';
    protected static string $updatedField = 'updated_at';






}