<?php

use App\Controllers\HomeController;
use App\Controllers\DevController;
use Core\Router;

/**
 * @var Router $router
 * 
 * If route caching is enabled, please make sure to clear cache.
 */


$router->get('/', [HomeController::class, 'index'])->name('home');
$router->get('/resume', 'HomeController@resume')->name('resume');
$router->get('/projects', 'HomeController@projects')->name('projects');
$router->match(['get', 'post'], '/contact', 'HomeController@contact')->name('contact');

$router->get('/dev', [DevController::class, 'index'])->name('dev');

$router->get('/json/(:alpha_spaces)', 'HomeController@json')->name('json');