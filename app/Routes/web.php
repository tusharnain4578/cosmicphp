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
$router->match(['get', 'post'], '/projects', 'HomeController@projects')->name('projects');
$router->get('/contact', 'HomeController@contact')->name('contact');

$router->get('/json/(:alpha_spaces)', 'HomeController@json')->name('json');