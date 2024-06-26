<?php

namespace App\Database\Migrations;

use Core\Database\Migration;

/**
 * up() and down() methods must return sql query string, which will get executed on running and
 * rollbacking the migration
 */
class create_users_table extends Migration
{
    private string $table = 'users';

    public function up(): string
    {
        return "
        CREATE TABLE IF NOT EXISTS {$this->table} (
            `id` bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            `full_name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `phone` varchar(30) NOT NULL,
            `password` varchar(255) NOT NULL,
            `role` int UNSIGNED NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL
        );
        ";
    }
    public function down(): string
    {
        return "DROP TABLE IF EXISTS {$this->table};";
    }
}