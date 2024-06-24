<?php

use Core\Router;

/**
 * @var Router $router
 */

$router->group('/', function (Router $router) {
    $router->get('/', 'HomeController::index')->name('home');
    $router->get('/contact', 'HomeController::contact')->name('contact');
    $router->get('/projects', 'HomeController::projects')->name('projects');
    $router->get('/resume', 'HomeController::resume')->name('resume');
});

