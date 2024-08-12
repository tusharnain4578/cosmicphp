<?php

namespace App\Config;


/**
 * Constants of this file must be public.
 * Methods of this file must be static and public
 */
class UtilityConfig
{
    const string ROUTE_GROUP_NAME_SEPARATOR = '.';




    /**
     * This function will be run on every input you use
     * NOTE* -> This function shoule always return the value
     */
    public static function request_input_gate(&$value)
    {
        if (is_string($value)) {
            $value = trim($value);
            if (empty($value))
                return null;
        }
        return $value;
    }


    /**
     * This method is used when calling Rex::now(),
     * Rex::now() is the base datetime for all the dates from Rex Class
     * If you'are using Rex Class for managing dates in your application, you can manipulate the app base datetime
     * 
     * NOTE * -> It must return date time in format 'Y-m-d H:i:s'
     */
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}