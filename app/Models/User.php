<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['full_name', 'email', 'phone', 'role', 'password'];
    protected static string $createdField = 'created_at';
    protected static string $updatedField = 'updated_at';




    public function wallet(): Wallet|null
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function posts(): array
    {
        return $this->hasMany(Post::class, 'user_id');
    }

}