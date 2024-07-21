<?php

namespace Core\Exceptions;


class SecurityException extends BaseException
{
    public const string ACTION_NOT_ALLOWED = 'actionNotAllowed';

    public static function actionNotAllowed(?string $filePath = null): static
    {
        $e = new static('The action you requested is not allowed.', 403);
        $e->type = self::ACTION_NOT_ALLOWED;
        return $e;
    }

}
