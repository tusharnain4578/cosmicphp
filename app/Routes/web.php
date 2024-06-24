<?php

use Core\Router;

/**
 * @var Router $router
 */


$router->get('/', 'HomeController::index')->name('home');
$router->get('/resume', 'HomeController::resume')->name('resume');
$router->get('/projects', 'HomeController::projects')->name('projects');
$router->get('/contact', 'HomeController::contact')->name('contact');
