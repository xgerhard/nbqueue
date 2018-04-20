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

$router->get('/', 'CommandController@QueryParser');

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
