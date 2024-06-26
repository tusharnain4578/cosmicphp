<?php

namespace App\Database\Migrations;

use Core\Database\Migration;

/**
 * up() and down() methods must return sql query string, which will get executed on running and
 * rollbacking the migration
 */
class create_admins_table extends Migration
{


    public function up(): string
    {
        return "
                CREATE TABLE IF NOT EXISTS admins (
            `id` bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL
        );
        ";
    }

    public function down(): string
    {
        return "";
    }
}