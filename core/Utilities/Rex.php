<?php

namespace Core\Utilities;

use App\Config\UtilityConfig;

/**
 * Date Utility Class
 */
class Rex
{
    private static string $timezone = "UTC";
    private static bool $isAppNowDefined = false;
    private const string DEFAULT_FORMAT = 'Y-m-d H:i:s';

    public static function init()
    {
        self::$timezone = env('TIMEZONE', 'UTC');
        date_default_timezone_set(self::$timezone);
        self::$isAppNowDefined = is_callable(UtilityConfig::class . "::now");
    }



    /**
     * This is the base of all dates
     */
    public static function now(): string
    {
        return self::$isAppNowDefined ? UtilityConfig::now() : date(self::DEFAULT_FORMAT);
    }

    public static function timestamp(): int
    {
        return strtotime(self::now());
    }

}