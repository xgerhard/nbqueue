<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\StatusController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/auth/{service}', [AuthController::class, 'AuthHandler']);

Route::get('/install/auto', [InstallController::class, 'startAuto']);
Route::post('/install/auto', [InstallController::class, 'installAuto']);
Route::get('/install/manual', [InstallController::class, 'startManual']);

Route::get('/status', [StatusController::class, 'index']);
Route::get('/', [CommandController::class, 'QueryParser']);

Route::get('/list/{channelId?}/{name?}', function ($channelId = null) {
    if(!$channelId)
        return redirect('/docs');
    else
        return redirect()->action(
            [QueueController::class, 'list'], ['channelId' => $channelId]
        );
});

Route::get('/{channelId}/{name?}', [QueueController::class, 'list']);