<?php
namespace Core;

use Core\Utilities\Arr;

class Autoload
{
    private const DOTENV_FILE_NAME = '.env';
    private const ENV_CACHE_PHP_FILE_NAME = 'env.php';


    /*
     *------------------------------------------------------------------------------------
     * Load ENV Method
     *------------------------------------------------------------------------------------
     */
    public static function loadEnv()
    {

        $envArray = cache()->getPHPFileCache(self::ENV_CACHE_PHP_FILE_NAME);

        if (!$envArray || !is_array($envArray)) {

            $envArray = [];

            $envFilePath = Path::rootPath(self::DOTENV_FILE_NAME);

            if (!file_exists($envFilePath))
                throw new \Exception("Environment file does not exist in project root directory.");

            // Parse .env file and prepare $envArray
            $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (empty($lines))
                return;
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0 || strpos(trim($line), '=') === false) {
                    continue;
                }

                list($name, $value) = explode('=', $line, 2);

                $name = trim($name);
                $value = trim($value);

                if (strpos($name, ' ') !== false)
                    throw new \Exception("Key $name can't have spaces between it");

                // Check for comments in the value
                if (($commentPos = strpos($value, '#')) !== false) {
                    $value = trim(substr($value, 0, $commentPos));
                }

                if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                    $value = $matches[1];
                } else {
                    if (strtolower($value) === 'true') {
                        $value = true;
                    } elseif (strtolower($value) === 'false') {
                        $value = false;
                    } elseif (is_numeric($value)) {
                        if (ctype_digit($value)) {
                            $value = (int) $value;
                        } else {
                            $value = (float) $value;
                        }
                    }
                }

                $envArray[$name] = $value;
            }

            $parsedEnvCache = Arr::array_to_php_return_file_string($envArray);
            cache()->setPHPFileCache(filename: self::ENV_CACHE_PHP_FILE_NAME, content: $parsedEnvCache);
        }

        foreach ($envArray as $name => $value) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }


    /*
     *------------------------------------------------------------------------------------
     * Helper Methods
     *------------------------------------------------------------------------------------
     */
    private static array $loadedHelpers = [];
    private const APP_HELPER_DIRECTORY = 'helpers';
    public static function loadHelper(string $helper, string $helperDirectoryPath = null)
    {
        $helperPath = '';

        if (!$helperDirectoryPath)
            $helperPath = Path::join(Path::appPath(), self::APP_HELPER_DIRECTORY, "$helper.php");
        else
            $helperPath = Path::join($helperDirectoryPath, "$helper.php");

        if (!file_exists($helperPath))
            throw new \Exception("Helper file doesnt exits or given wrong name.");

        self::$loadedHelpers[] = $helperPath;

        require_once $helperPath;
    }
}