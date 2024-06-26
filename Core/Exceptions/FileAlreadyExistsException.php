<?php

namespace Core\Exceptions;

use \Exception;
use \Throwable;

class FileAlreadyExistsException extends Exception
{
    public function __construct($message = "File already exists.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
