<?php

namespace App\Database\Migrations;

use Core\Database\Migration;

/**
 * up() and down() methods must return sql query string, which will get executed on running and
 * rollbacking the migration
 */
class create_wallets_table extends Migration
{
    private string $table = 'wallets';

    public function up(): string
    {
        return "
             CREATE TABLE IF NOT EXISTS {$this->table} (
            `id` bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            `user_id` bigint UNSIGNED UNIQUE KEY NOT NULL,
            `balance` bigint NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE
        );
        ";
    }

    public function down(): string
    {
        return "DROP TABLE IF EXISTS {$this->table};";
    }
}