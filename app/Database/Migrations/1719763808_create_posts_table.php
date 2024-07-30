<?php

namespace App\Database\Migrations;

use Core\Interfaces\IMigration;

/**
 * up() and down() methods must return sql query string, which will get executed on running and
 * rollbacking the migration
 */
class create_posts_table implements IMigration
{
    private string $table = 'posts';

    public function up(): string|array
    {
        return "
        CREATE TABLE IF NOT EXISTS {$this->table} (
            `id` bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            `user_id` bigint UNSIGNED NOT NULL,
            `title` varchar(255) NOT NULL,
            `content` text NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE
        );
        ";
    }

    public function down(): string|array
    {
        return "DROP TABLE IF EXISTS {$this->table};";
    }
}
