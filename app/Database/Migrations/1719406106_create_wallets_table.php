<?php

namespace App\Database\Migrations;

use Core\Database\Migration;

class create_wallets_table extends Migration
{
    private string $table = 'wallets';

    public function up(): string
    {
        return "
            CREATE TABLE IF NOT EXISTS {$this->table} (
                `id` bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
                `user_id` bigint UNSIGNED NOT NULL,
                `balance` decimal(28, 8) NOT NULL DEFAULT 0.00,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                FOREIGN KEY (`user_id`) REFERENCES users(`id`)
                    ON UPDATE RESTRICT
                    ON DELETE RESTRICT
                );
            ";
    }

    public function down(): string
    {
        return "DROP TABLE IF EXISTS {$this->table};";
    }
}