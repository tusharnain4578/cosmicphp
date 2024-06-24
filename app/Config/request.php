<?php

namespace App\Config;

/**
 * Constants of this file must be public.
 * Methods of this file must be static and public
 */
class request
{

    /**
     * This function will be run on every input you use
     * NOTE* -> This function shoule always return the value
     */
    public static function input_gate(&$value)
    {
        if (is_string($value)) {
            $value = trim($value);
            if (empty($value))
                return null;
        }
        return $value;
    }

}