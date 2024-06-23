<?php

use Core\Router;

/**
 * @var Router $router
 */

$router->get('/', 'HomeController::index')->name('home');
$router->get('/contact', 'HomeController::contact')->name('contact');
$router->get('/projects', 'HomeController::projects')->name('projects');
$router->get('/resume', 'HomeController::resume')->name('resume');

$router->group('user', ['name' => 'user'], function (Router $router) {

    $router->get('/', function () {
        return 'hello!';
    });


    $router->group('dash', ['name' => 'dash'], function (Router $router) {
        $router->get('/', function () {
            return 'dash';
        })->name('dash');
    });

});
