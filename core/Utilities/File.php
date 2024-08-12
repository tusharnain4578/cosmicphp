<?php

namespace Core\Utilities;

use Core\Exceptions\FileException;
use Core\Utilities\Path;

class File
{

    public static function exists(string $filePath): bool
    {
        return file_exists($filePath);
    }

    public static function create(string $filePath, string $content)
    {
        if (file_exists($filePath))
            throw FileException::fileAlreadyExists($filePath);

        $dirname = dirname($filePath);
        if (!file_exists($dirname))
            mkdir(directory: $dirname, recursive: true);

        file_put_contents($filePath, $content);
    }

    public static function delete(string $filePath, $throwExceptionIfFileNotExists = false)
    {
        if (!file_exists($filePath)) {
            if ($throwExceptionIfFileNotExists)
                throw new \Exception("FIle: $filePath, does not exists, when trying to delete.");
            return;
        }

        unlink($filePath);
    }


    public static function scan_directory(string $directory, bool $returnFullPath = false): array
    {
        $fileNames = array_diff(scandir($directory), ['.', '..']);

        if (!$returnFullPath)
            return $fileNames;

        $fullPathArray = [];
        foreach ($fileNames as &$fileName)
            $fullPathArray[] = Path::join($directory, $fileName);

        return $fullPathArray;
    }

    public static function getAllFilesInDirectory(string $directory, ?string $ext = null, bool $recursive = false): array
    {
        $result = [];
        $dir = [$directory];

        while (($currentDir = array_shift($dir)) && ($files = glob($currentDir . '/*'))) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (!$ext || pathinfo($file, PATHINFO_EXTENSION) === $ext)
                        $result[] = $file;
                } elseif ($recursive && is_dir($file))
                    $dir[] = $file;
            }
        }

        return $result;
    }

}