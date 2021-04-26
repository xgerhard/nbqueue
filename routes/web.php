<?php

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
use App\src\Channel;
use App\src\Queue;
use App\src\QueueUser;
use App\src\User;

$router->get('auth/{service}', 'AuthController@AuthHandler');

$router->get('install/auto', 'InstallController@startAuto');
$router->post('install/auto', 'InstallController@installAuto');
$router->get('install/manual', 'InstallController@startManual');

$router->get('/', 'CommandController@QueryParser');

// Remove /list/ from the subdomain
$strListPath = isset($_SERVER['HTTP_HOST']) ? (explode('.', $_SERVER['HTTP_HOST'])[0] === 'nbq' ? '' : 'list') : '';

$router->get($strListPath .'/{channelId}[/{name}]', 'QueueController@list');