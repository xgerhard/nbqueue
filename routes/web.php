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
$strListPath = explode('.', $_SERVER['HTTP_HOST'])[0] === 'nbq' ? '' : 'list';

$router->get($strListPath .'/{channelId}[/{name}]', 'QueueController@list');

/*$router->get('/', function () use ($router) {
    return $router->app->version();
});*


/*
Server commands
----
!setq $name // Switches queue channel
!currq // Display current queue name and list info


!next (!q next) $number // Pick next $number users from queue
!random (!q random) // Pick random user from queue
!open (!q open) // Open queue
!close (!q close) // Close queue
!add (!q add) $user "message" // Adds user to queue
!remove (!q remove) $user // Removes user from queue

// Settings
!q setsize 15
!q 

User commands
---

!join (!q join) "optional message"
!q leave
*/
