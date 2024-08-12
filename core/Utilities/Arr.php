<?php

namespace Core\Utilities;

class Arr
{
    /**
     * Valid Array, Key(String), Value(String)
     * 
     * This function will take an associative array as arguement, and return a php file content which is returning that array, works with nested arrays also.
     * 
     * Can be used to create a .php file (which returns as array)
     * 
     */
    public static function array_to_php_return_file_string(array $arr, bool $minimized = false)
    {
        $arrayToString = function ($arr, $level = 1) use (&$arrayToString, $minimized) {
            $content = $minimized ? "[" : "[\n";
            foreach ($arr as $key => $value) {
                $indent = $minimized ? "" : str_repeat('    ', $level);
                $keyString = is_string($key) ? "'$key'" : $key;

                if (is_array($value)) {
                    $valueString = $arrayToString($value, $level + 1);
                    $content .= "{$indent}{$keyString} => {$valueString},";
                } elseif (is_string($value)) {
                    $valueString = "'" . addslashes($value) . "'";
                    $content .= "{$indent}{$keyString} => {$valueString},";
                } elseif (is_numeric($value) || is_bool($value)) {
                    $valueString = var_export($value, true);
                    $content .= "{$indent}{$keyString} => {$valueString},";
                } else {
                    throw new \Exception("Invalid value during array parsing, only string, number, boolean, and array are allowed");
                }

                // Ensure each element ends with a newline if not minimized
                if (!$minimized) {
                    $content .= "\n";
                }
            }

            // Trim trailing comma and add closing bracket
            $content = rtrim($content, ",") . ($minimized ? "]" : str_repeat('    ', $level - 1) . "]");
            return $content;
        };

        $content = "<?php\n\nreturn " . $arrayToString($arr) . ";\n";
        return $content;
    }


}