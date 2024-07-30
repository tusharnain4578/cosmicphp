<?php

namespace App\Database\Migrations;

use Core\Interfaces\IMigration;

/**
 * up() and down() methods must return sql query string, which will get executed on running and
 * rollbacking the migration
 */
class create_customers_table implements IMigration
{

    private string $table = 'customers';
    public function up(): string|array
    {
        return "
        CREATE TABLE IF NOT EXISTS {$this->table} (
            `id` bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            `customer_name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL
        );
        ";
    }

    public function down(): string|array
    {
        return "DROP TABLE IF EXISTS {$this->table};";
    }
}