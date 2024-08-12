<?php

namespace Core\Exceptions;


class FileException extends BaseException
{
    public const string FILE_ALREADY_EXISTS_EXCEPTION = 'fileAlreadyExists';

    public static function fileAlreadyExists(?string $filePath = null): static
    {
        $e = new static(($filePath ? "'$filePath' : " : '') . 'File already exists.');
        $e->type = self::FILE_ALREADY_EXISTS_EXCEPTION;
        return $e;
    }


}
