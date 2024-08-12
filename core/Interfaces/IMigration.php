<?php

namespace Core\Interfaces;

/**
 * Interface IMigration
 * 
 * This interface defines the structure for database migration classes.
 * Implementations of this interface should provide the SQL statements or 
 * commands necessary to apply (up) or revert (down) the migration.
 */
interface IMigration
{
    /**
     * Get the SQL statements or commands to apply the migration.
     *
     * This method should return either a single SQL statement as a string
     * or an array of SQL statements as strings. Each string represents an
     * individual SQL statement or command that should be executed to apply
     * the migration.
     *
     * @return string|string[] The SQL statement(s) for applying the migration.
     */
    public function up(): string|array;

    /**
     * Get the SQL statements or commands to revert the migration.
     *
     * This method should return either a single SQL statement as a string
     * or an array of SQL statements as strings. Each string represents an
     * individual SQL statement or command that should be executed to revert
     * the migration.
     *
     * @return string|string[] The SQL statement(s) for reverting the migration.
     */
    public function down(): string|array;
}
