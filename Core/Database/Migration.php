<?php

namespace Core\Database;

use Core\Console\Console;
use Core\Utilities\File;
use Core\Utilities\Path;
use Core\Utilities\Rex;

abstract class Migration
{
    private const MIGRATION_TABLE = 'migrations';
    public abstract function up(): string;
    public abstract function down(): string;

    public static function runMigrations()
    {
        $MigFileData = array_merge(
            self::getAllMigrationFileData(Path::join(Path::frameworkPath('Database'), 'Migrations'), framework: true),
            self::getAllMigrationFileData(Path::join(Path::appPath('Database'), 'Migrations'))
        );

        $defaultDb = db();

        // get migration logs from database
        $executedMigrations = $defaultDb->tableExists(self::MIGRATION_TABLE) ? $defaultDb->table(self::MIGRATION_TABLE)->get()->result() : [];
        $executedMigrationsCount = count($executedMigrations);

        $batch = $executedMigrationsCount === 0 ? 1 : $executedMigrations[$executedMigrationsCount - 1]->batch + 1;


        $migrationExecutedCount = 0;

        // Looping through app migrations

        foreach ($MigFileData as $filedata) {

            $class = $filedata['class'];
            $filename = $filedata['filename'];

            try {


                $obj = new $class();

                if (method_exists($obj, method: 'up')) {

                    $query = trim($obj->up());

                    if (empty($query))
                        throw new \Exception("Migration : '$class' has empty query returned!");

                    $group = (property_exists($obj, 'group') && !empty(trim($obj->group ?? ''))) ? $obj->group : 'default';
                    $filedata['group'] = $group;

                    // checking if already executed
                    $isExecutedBefore = (function () use (&$executedMigrations, &$filedata): bool{
                        foreach ($executedMigrations as &$mig) {
                            if ($filedata['framework'] || ($mig->class === $filedata['class'] && $mig->filename === $filedata['filename'] && $mig->group === $filedata['group']))
                                return true;
                        }
                        return false;
                    })();

                    if ($isExecutedBefore)
                        continue;

                    db($group)->query($query);

                    if (!(property_exists($obj, 'isCore') && $obj->isCore)) {
                        // If this is app migration, only then we will log it+

                        db()->table(self::MIGRATION_TABLE)->insert([
                            'class' => $class,
                            'filename' => $filename,
                            'group' => $group,
                            'batch' => $batch,
                            'created_at' => Rex::now()
                        ]);

                        Console::success("Migration executed : $filename");

                        $migrationExecutedCount++;
                    }
                }

            } catch (\Exception $e) {
                Console::error("Error executing migration : $filename");
                throw $e;
            }

        }

        if ($migrationExecutedCount === 0) {
            Console::success("Nothing to migrate!");
        }
    }

    private static function getAllMigrationFileData(string $directory, bool $framework = false): array
    {
        $migFiles = File::scan_directory($directory, returnFullPath: true);
        natcasesort($migFiles);

        $classesBefore = get_declared_classes();
        foreach ($migFiles as $file)
            require_once $file;
        $classesAfter = get_declared_classes();

        $classes = array_diff($classesAfter, $classesBefore);
        $classesFirstKey = array_key_first($classes);

        if (count($migFiles) !== count($classes))
            throw new \Exception("Irregularity in migration files and classes. Each migration file should have only 1 class defined.");

        foreach ($migFiles as $index => $file)
            $migFiles[$index] = ['class' => $classes[$classesFirstKey + $index], 'filename' => basename($file), 'framework' => $framework];

        return $migFiles;
    }

    public static function handleCommand(array $args)
    {
        $param = $args[0];

        switch ($param) {
            case 'migrate':
                self::runMigrations();
                break;
            default:
                throw new \Exception("Invalid parameter : $param");
        }
    }

}