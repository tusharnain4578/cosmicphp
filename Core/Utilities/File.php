<?php

namespace Core\Utilities;

use Core\Utilities\Path;

class File
{

    public static function exists(string $filePath): bool
    {
        return file_exists($filePath);
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

}