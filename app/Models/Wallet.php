<?php

namespace App\Models;

use Core\Model;

class Wallet extends Model
{
    protected static string $table = 'wallets';
    protected static array $fillable = ['user_id', 'balance'];
    protected static string $createdField = 'created_at';
    protected static string $updatedField = 'updated_at';






}