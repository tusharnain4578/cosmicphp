<?php

use App\Controllers\HomeController;
use Core\Router;

/**
 * @var Router $router
 * 
 * If route caching is enabled, please make sure to clear cache.
 */


$router->get('/', [HomeController::class, 'index'])->name('home');
$router->get('/resume', 'HomeController@resume')->name('resume');
$router->get('/projects', 'HomeController@projects')->name('projects');
$router->get('/contact', 'HomeController@contact')->name('contact');


$router->match(['get', 'post'], 'login', [HomeController::class, 'login']);