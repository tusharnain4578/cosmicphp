<?php

namespace App\Models;

use Core\Base\Model;

class Post extends Model
{
    protected static string $table = 'posts';
    protected static array $fillable = ['user_id', 'title', 'content'];
    protected static string $createdField = 'created_at';
    protected static string $updatedField = 'updated_at';




}