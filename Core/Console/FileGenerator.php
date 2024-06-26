<?php

namespace Core\Console;

use Core\Exceptions\FileAlreadyExistsException;
use Core\Utilities\File;
use Core\Utilities\Path;
use Core\Utilities\Rex;
use Core\View;

class FileGenerator
{
    /**
     * @var list<string>
     */
    private static array $args = [];
    private const DATA = [
        'migration' => ['namespace' => 'App\Database\Migrations', 'app_dir' => 'Database/Migrations'],
        'controller' => ['namespace' => 'App\Controllers', 'app_dir' => 'Controllers'],
        'middleware' => ['namespace' => 'App\Middlewares', 'app_dir' => 'Middlewares'],
        'view' => ['app_dir' => View::TEMPLATE_DIRECTORY],
    ];



    private static function createFile(string $name, string $fileType, string $ucFirstFileType)
    {

        $fileData = self::getFileData($name, $fileType);

        $filepath = Path::appPath($fileData['filepath']);
        $filecontent = $fileData['filecontent'];

        try {

            File::create($filepath, $filecontent);

        } catch (FileAlreadyExistsException $e) {

            Console::error("$ucFirstFileType : '$name' already exists!");

        }
    }



    private static function getFileData(string $name, string $fileType): array
    {
        $dir = self::DATA[$fileType]['app_dir'];
        $namespace = self::DATA[$fileType]['namespace'] ?? null;
        $nameParts = explode('/', $name);
        $className = array_pop($nameParts);
        $stub = self::getStubString($fileType);

        switch ($fileType) {
            case 'migration': {
                $group = Console::getArgFlagValue(2, '--group', 'default');

                if (!self::validateArg($group, 'migration:group'))
                    throw new \InvalidArgumentException("Migration group : '$group' is in invalid format.");
                $isDefault = $group === 'default';
                $className = $isDefault ? $className : "{$group}_{$className}";
                $groupField = $isDefault ? '' : "public string \$group = '$group';";
                $filecontent = str_replace(
                    ['{{namespace}}', '{{classname}}', '{{group_field}}'],
                    [$namespace, $className, $groupField],
                    $stub
                );
                $filename = $isDefault ? '' : "{$group}_";
                $filename = $filename . Rex::timestamp() . "_$name.php";

                return [
                    'filepath' => "$dir/$filename",
                    'filecontent' => $filecontent
                ];
            }
            case 'middleware':
            case 'controller': {
                $filepath = "{$dir}/{$name}.php";
                $namespace = self::getAppendedNamespace($namespace, $name);
                $filecontent = str_replace(['{{namespace}}', '{{classname}}'], [$namespace, $className], $stub);
                return [
                    'filepath' => $filepath,
                    'filecontent' => $filecontent
                ];
            }
            case 'view': {
                $nameWithExt = $name . View::TEMPLATE_EXTENSION;
                return [
                    'filepath' => "{$dir}/{$nameWithExt}",
                    'filecontent' => str_replace('{{filename}}', $nameWithExt, $stub)
                ];
            }
        }

        throw new \Exception("Invalid filetype : '$fileType'");
    }



    private static function getAppendedNamespace(string $namespace, string $filename): string
    {
        if ($lastSlashPos = strrpos($filename, '/')) {
            $appendNamespace = str_replace('/', '\\', substr($filename, 0, $lastSlashPos));
            $namespace = $namespace .= "\\$appendNamespace";
        }
        return $namespace;
    }
    private static function getStubString(string $name): string
    {
        $filePath = Path::frameworkPath("resources/stubs/$name.txt");
        if (!File::exists($filePath))
            throw new \Exception("Stub : '$filePath' doesnt't exists!");
        return file_get_contents($filePath);
    }

    private static function validateArg(string $name, string $fileType): bool
    {
        switch ($fileType) {
            case 'migration': {
                if (!preg_match('/^[a-zA-Z][a-zA-Z_]*$/', $name))
                    return false;
            }
            case 'migration:group': {
                if (!preg_match('/^[a-zA-Z][a-zA-Z_]*[a-zA-Z]$/', $name))
                    return false;
            }
            case 'view':
            case 'middleware':
            case 'controller': {
                if (!preg_match('/^[a-zA-Z](?:[a-zA-Z_\/]*[a-zA-Z])?$/', $name))
                    return false;
            }
        }
        return true;
    }
    public static function handleConsole(array $args)
    {
        self::$args = $args;
        $param = self::$args[0];

        // $args[0] is the param
        if (
            !empty($param) &&
            ($parts = explode(':', $param)) &&
            (count($parts) === 2) &&
            ($parts[0] === 'create') &&
            (!empty($parts[1]))
        ) {

            $fileType = $parts[1];

            if (!in_array($fileType, array_keys(self::DATA)))
                throw new \Exception("Invalid file : '$fileType' given to create.");


            $ucFirstFileType = ucfirst($fileType);

            $name = self::$args[1] ?? Console::askInLoop(
                question: "Enter $ucFirstFileType Name : ",
                errorMessage: "$ucFirstFileType name cannot be empty!"
            );

            if (!self::validateArg($name, $fileType))
                throw new \Exception("$ucFirstFileType : '$name' is invalid.");

            self::createFile($name, $fileType, $ucFirstFileType);


        } else {
            CLI::invalidParamMessage();
        }
    }
}