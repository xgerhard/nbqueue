<?php
namespace App\Http\Controllers;

use App\OAuth\OAuthHandler;
use Illuminate\Http\Request;
use Exception;
use Log;

class AuthController
{
    public function AuthHandler(Request $request, $strService)
    {
        try
        {
            $OAuthHandler = new OAuthHandler($strService);
            $OAuthHandler->runAuth($request);
        }
        catch(Exception $e)
        {
            Log::error($e);
            echo 'Something went wrong, please try again later.';
        }
    }
}
?>