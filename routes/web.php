<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

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
    return response()->json(['status' => true, 'message' => 'Authy API version 1.0']);
});

$router->post('/dummy-data', 'DummyController@getDummyData');
$router->post('/dummy-data/restructured', 'DummyController@getNewDummyData');
$router->post('/dummy-data/refresh', 'DummyController@refresh');

$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/register', 'AuthController@register');
$router->post('/auth/check-email', 'AuthController@checkEmail');

$router->group(['middleware' => 'auth'], function ($router) {
    $router->get('/user', 'UserController@profile');
});
