<?php

namespace App\Database\Migrations;

use Core\Database\Migration;

/**
 * up() and down() methods must return sql query string, which will get executed on running and
 * rollbacking the migration
 */
class update_users_table extends Migration
{


    public function up(): string
    {
        return "
        ALTER TABLE `users`
        RENAME COLUMN `full_name` TO `name`
        ";
    }

    public function down(): string
    {
        return "
        ALTER TABLE `users`
        RENAME COLUMN `name` TO `full_name`
        ";
    }
}