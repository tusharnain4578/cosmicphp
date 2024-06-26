<?php

namespace Core\Database\Migrations;

use Core\Database\Migration;

/**
 * up() and down() methods must return sql query string, which will get executed on running and
 * rollbacking the migration
 */
class create_migrations_table extends Migration
{
    public string $group = 'default';
    private string $table = 'migrations';
    public bool $isCore = true;
    public function up(): string
    {
        return "
            CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            `class` varchar(255) NOT NULL UNIQUE KEY,
            `filename` varchar(255) NOT NULL UNIQUE KEY,
            `group` varchar(255) NOT NULL,
            `batch` int UNSIGNED NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL
            ) ENGINE=InnoDB;
        ";
    }
    public function down(): string
    {
        return "DROP TABLE IF EXISTS {$this->table};";
    }
}