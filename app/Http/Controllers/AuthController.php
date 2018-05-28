<?php
namespace App\Http\Controllers;

use App\OAuth\OAuthHandler;
use Illuminate\Http\Request;

class AuthController
{
    public function AuthHandler(Request $request, $strService)
    {
        try
        {
            $OAuthHandler = new OAuthHandler($strService);
            $OAuthHandler->runAuth($request);
        }
        catch (Exception $e)
        {
            dd($e);
        }
    }
}
?>