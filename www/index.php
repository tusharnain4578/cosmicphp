<?php

// this index.php should always be inside public folder to prevent security breach of accessing root direcotry files

declare(strict_types=1);
error_reporting(-1);
ini_set('display_errors', '1');

require_once (__DIR__ . '/../vendor/autoload.php');


/**
 * Some Constants
 */
define('FCPATH', __DIR__);

$core = new \Core\App;
$core->run();