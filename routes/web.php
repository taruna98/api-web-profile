<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// profile
$router->get('profile/{id}', 'ProfileController@index');

// portfolio
$router->get('portfolio/{id}', 'PortfolioController@index');
$router->get('portfolio/detail/{id}', 'PortfolioController@detail');

// article
$router->get('article/{id}', 'ArticleController@index');
$router->get('article/detail/{id}', 'ArticleController@detail');