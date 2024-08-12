<?php
namespace Core;

use Core\Utilities\Arr;
use Core\Utilities\Classic;
use Core\Utilities\Path;
use App\Config\env as envConfig;

class Autoload
{
    private const DOTENV_FILE_NAME = '.env';
    public const ENV_CACHE_PHP_FILE_NAME = 'core_env.php';


    /*
     *------------------------------------------------------------------------------------
     * Load ENV Method
     *------------------------------------------------------------------------------------
     */
    public static function loadEnv()
    {

        // first getting environment variables from app -> config -> Env.php
        $envDefaultArray = envConfig::VARS ?? [];
        $envCacheArray = cache()->getPHPFileCache(self::ENV_CACHE_PHP_FILE_NAME) ?? [];
        $envArray = array_merge($envDefaultArray, $envCacheArray);

        if (!$envCacheArray || !is_array($envCacheArray)) {

            $envFilePath = Path::rootPath(self::DOTENV_FILE_NAME);

            if (file_exists($envFilePath)) {
                // Parse .env file and prepare $envArray
                $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if (empty($lines))
                    return;
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0 || strpos(trim($line), '=') === false)
                        continue;

                    list($name, $value) = explode('=', $line, 2);

                    $name = trim($name);
                    $value = trim($value);

                    if (strpos($name, ' ') !== false)
                        throw new \Exception("Key $name can't have spaces between it");

                    // Check for comments in the value
                    if (($commentPos = strpos($value, '#')) !== false)
                        $value = trim(substr($value, 0, $commentPos));

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

                $parsedEnvCache = Arr::array_to_php_return_file_string($envArray, minimized: true);
                cache()->setPHPFileCache(filename: self::ENV_CACHE_PHP_FILE_NAME, content: $parsedEnvCache);
            }
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
    public static function loadHelper(string|array $helperName, string $helperDirectoryPath = null)
    {
        $helpers = is_string($helperName) ? [$helperName] : $helperName;


        foreach ($helpers as &$helper) {
            $helperPath = '';

            if (!$helperDirectoryPath)
                $helperPath = Path::join(Path::appPath(self::APP_HELPER_DIRECTORY), "$helper.php");
            else
                $helperPath = Path::join($helperDirectoryPath, "$helper.php");

            if (!file_exists($helperPath))
                throw new \Exception("Helper file doesnt exits or given wrong name.");

            self::$loadedHelpers[] = $helperPath;

            require_once $helperPath;
        }
    }


    // 
    public static function loadAppHelpers()
    {
        $helpers = \App\Config\autoload::AUTOLOAD_HELPERS;
        self::loadHelper($helpers);
    }
}