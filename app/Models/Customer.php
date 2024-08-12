<?php

namespace App\Models;

use Core\Base\Model;

class Customer extends Model
{
    protected static string $table = 'customers';
    protected static string $createdField = 'created_at';
    protected static array $fillable = ['customer_name', 'email', 'password'];





}