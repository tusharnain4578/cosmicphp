<?php

namespace Core\Database;

use Core\Console\Console;
use Core\Utilities\Path;
use Core\Console\CLI;
use Core\Utilities\File;

class Migration
{
    const MIGRATION_TABLE = 'migrations';
    private static function addMigrationEntry(string $filename, string $group)
    {
        $data = [
            'file_name' => $filename,
            'group' => $group,
            'batch' => 2,
            'created_at' => date('Y-m-d H:i:s')
        ];

    }



    private static function getNecessaryMigrationFiles(): array
    {
        $fwMigrationsDir = Path::join(Path::frameworkPath(), 'Database', 'Migrations');

        $fwMigFiles = File::scan_directory($fwMigrationsDir);

        $fwMigFiles = array_map(fn($file) => [
            'framework' => true,
            'filename' => $file,
            'filepath' => Path::join($fwMigrationsDir, $file)
        ], $fwMigFiles);

        return $fwMigFiles;
    }

    public static function migrateMigrations()
    {

        $migFiles = self::getNecessaryMigrationFiles();

        $migrationsDir = Path::join(Path::appPath(), 'Database', 'Migrations');

        $files = File::scan_directory($migrationsDir);

        $files = array_map(fn($file) => ['filename' => $file, 'filepath' => Path::join($migrationsDir, $file)], $files);


        // appending files into $migFiles
        $allMigFiles = array_merge($migFiles, $files);

        foreach ($allMigFiles as $file) {
            $path = $file['filepath'];
            $filename = $file['filename'];
            $isFramework = isset($file['framework']);



        }

        Console::success("Successfully Executed all migration!");
    }



    public static function handleCommand(array $args)
    {
        $param = $args[0];

        if ($param === 'migrate') {
            self::migrateMigrations();
        } else if ($param === 'migrate:rollback') {
            dd('rollbacking..., lol feature not implemented yet!');
        } else {
            CLI::invalidParamMessage();
        }

    }
}