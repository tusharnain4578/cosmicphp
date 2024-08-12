<?php

namespace Core\Exceptions;

use \Exception;

class BaseException extends Exception
{
    protected string $type = '';


    public function typeOf(string $type): bool
    {
        return $this->type === $type;
    }

}
