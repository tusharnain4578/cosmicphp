<?php

namespace Framework\Utilities;

class Arr
{
    /**
     * Valid Array, Key(String), Value(String)
     * 
     * This function will take an associative array as arguement, and return a php file content which is returning that array
     * 
     * Can be used to create a .php file (which returns as array)
     * 
     */
    public static function array_to_php_return_file_string(array $arr)
    {
        $content = "<?php\n\nreturn [\n";
        foreach ($arr as $key => $value) {
            if (!is_string($value) && !is_numeric($value) && !is_bool($value))
                throw new \Exception("Invalid value during array parsing, only string, number and boolean is allowed");
            $content .= "    '{$key}' => ";
            if (is_numeric($value) || is_bool($value))
                $content .= var_export($value, true) . ",\n";
            else
                $content .= "'" . addslashes($value) . "',\n";
        }
        $content .= "];\n";
        return $content;
    }
}