<?php

namespace Core\Console;

use Core\Exceptions\FileAlreadyExistsException;
use Core\Utilities\File;
use Core\Utilities\Path;
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
        'view' => ['app_dir' => View::TEMPLATE_DIRECTORY],
    ];



    private static function createClassFile(string $name, string $fileType, string $ucFirstFileType)
    {
        $stub = self::getStubString($fileType);

        $dir = self::DATA[$fileType]['app_dir'];
        $nameParts = explode('/', $name);

        $namespace = self::getFileNamespace($name, $fileType);

        if ($namespace) {
            $ext = '.php';
            $className = array_pop($nameParts);
            $fileContent = str_replace(['{{namespace}}', '{{classname}}'], [$namespace, $className], $stub);
        } else {
            $fileContent = str_replace('{{filename}}', $name, $stub);
            $ext = View::TEMPLATE_EXTENSION;
        }

        $fullFilePath = Path::appPath("$dir/$name.$ext");

        try {
            File::create($fullFilePath, $fileContent);
        } catch (FileAlreadyExistsException $e) {
            Console::error("$ucFirstFileType : '$name' already exists!");
        }
    }




    private static function getFileNamespace(string $name, string $fileType): string|null
    {
        switch ($fileType) {
            case 'migration': {
                return self::DATA[$fileType]['namespace'];
            }
            case 'controller': {
                $namespace = self::DATA[$fileType]['namespace'];
                if ($lastSlashPos = strrpos($name, '/')) {
                    $appendNamespace = str_replace('/', '\\', substr($name, 0, $lastSlashPos));
                    $namespace = $namespace .= "\\$appendNamespace";
                }
                return $namespace;
            }
            case 'view': {
                return null;
            }
        }

        throw new \Exception("Invalid filetype : '$fileType'");
    }

    private static function getStubString(string $name): string
    {
        $filePath = Path::frameworkPath("resources/stubs/$name.txt");
        if (!File::exists($filePath))
            throw new \Exception("Stub : '$filePath' doesnt't exists!");
        return file_get_contents($filePath);
    }

    private static function validateName(string $name, string $fileType): bool
    {
        switch ($fileType) {
            case 'migration': {
                if (!preg_match('/^[a-zA-Z][a-zA-Z_]*$/', $name))
                    return false;
            }
            case 'view':
            case 'controller': {
                if (!preg_match('/^[a-zA-Z][a-zA-Z_\/]*[a-zA-Z]$/', $name))
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

            if (!in_array($fileType, array_keys(self::DATA))) {
                Console::error("Invalid file given to create.");
                return;
            }

            $ucFirstFileType = ucfirst($fileType);

            $name = self::$args[1] ?? Console::askInLoop(
                question: "Enter $ucFirstFileType Name : ",
                errorMessage: "$ucFirstFileType name cannot be empty!"
            );

            if (!self::validateName($name, $fileType)) {
                Console::error("$ucFirstFileType Name : '$name' is invalid.");
                return;
            }

            self::createClassFile($name, $fileType, $ucFirstFileType);


        } else {
            CLI::invalidParamMessage();
        }
    }
}