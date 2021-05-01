<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class StatusController extends Controller
{
    public function index()
    {
        echo '<h3>Status:</h3>';
        echo 'Channels: '. DB::table('channels')->count() .'<br/>';
        echo 'Queues: '. DB::table('queues')->count() .'<br/>';
        echo 'Users: '. DB::table('users')->count() .'<br/>';
        echo 'Users in queue: '. DB::table('queue_users')->count();
    }
}