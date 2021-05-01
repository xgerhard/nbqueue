<?php

namespace App\OAuth;

use Exception;
use App\OAuth\OAuthProvider;
use App\OAuth\OAuthSession;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Log;

class OAuthHandler
{
    public $provider;

    public function __construct($strService)
    {
        $this->setProvider($strService);
    }

    public function runAuth($request)
    {
        // If code is set, user authorized or app
        if($request->input('code') !== null)
        {
            // Validate state
            if(
                $request->input('state') === null ||
                !session()->has('state') ||
                $request->input('state') != session()->pull('state')
            )
            throw new Exception('Invalid state parameter');

            // Get access tokens with access code
            $oTokens = $this->getTokens($request->input('code'));
            if(isset($oTokens->name)) $this->handleError($oTokens->name); // Nightbot error field

            // Save tokens
            $OAuthSession = new OAuthSession;
            $OAuthSession->access_token = $oTokens->access_token;
            $OAuthSession->refresh_token = $oTokens->refresh_token;
            $OAuthSession->expires_in = Carbon::now()->addSeconds($oTokens->expires_in);
            $OAuthSession->refresh_expires_in = Carbon::now()->addSeconds(isset($oTokens->refresh_expires_in) ? $oTokens->refresh_expires_in : 5184000); // 60 Days default.
            $OAuthSession->provider_id = $this->provider->id;
            $OAuthSession->save();

            session()->put($this->provider->name .'-auth', $OAuthSession->id);

            redirect($this->provider->local_redirect)->send();
            //return $OAuthSession->access_token;
        }

        // If user denied authorization an error will be returned
        elseif($request->input('error') !== null)
        {
            $this->handleError($request->input('error'));
        }

        // Else it will be a new auth request
        else
        {
            // Go Auth!
            redirect()->to($this->getAuthUrl())->send();
        }
        return false;
    }

    /*
    * getAuthUrl
    * Build the auth url and save state to session
    * return (string) auth url
    */
    public function getAuthUrl()
    {
        // Create and save state
        $strState = $this->generateState();
        session()->put('state', $strState);

        // Build url
        return $this->provider->auth_url .'&state='. $strState .'&client_id='. $this->provider->client_id 
        . (isset($this->provider->scope) ? '&scope='. $this->provider->scope : '')
        . (isset($this->provider->redirect_url) ? '&redirect_uri='. urlencode($this->provider->redirect_url) : '');
    }

    /*
    * generateState
    * Generate random string to validate user
    * return (string) random string
    */
    private function generateState()
    {
        return sha1(time() . rand(1, 9999));
    }

    /*
    * getTokens
    * Requests tokens to auth server
    * required (string) code / refresh token
    * optional (boolean) refresh, true for refresh token, false for code
    */
    private function getTokens($strCode, $bRefresh = false)
    {
        $oClient = new Client([
            'http_errors' => false,
            // 'verify' => false
        ]);

        $oResponse = $oClient->request('POST', $this->provider->token_url, [
            'form_params' => [
                'grant_type' => $bRefresh === false ? 'authorization_code' : 'refresh_token',
                $bRefresh === false ? 'code' : 'refresh_token' => $strCode,
                'client_id'    => $this->provider->client_id,
                'client_secret' => $this->provider->client_secret
            ]
        ]);
        return json_decode($oResponse->getBody()->getContents());
    }

    public function getAppTokens()
    {
        $a = [
            'client_id'    => $this->provider->client_id,
            'client_secret' => $this->provider->client_secret,
            'grant_type' => 'client_credentials'
        ];

        $oClient = new Client([
            'http_errors' => false
        ]);

        $oResponse = $oClient->request('POST', $this->provider->token_url .'?'. http_build_query($a));
        $oTokens = json_decode($oResponse->getBody()->getContents());

        if(isset($oTokens->access_token) && isset($oTokens->expires_in))
        {
            $OAuthSession = new OAuthSession;
            $OAuthSession->access_token = $oTokens->access_token;
            $OAuthSession->expires_in = Carbon::now()->addSeconds($oTokens->expires_in);
            $OAuthSession->refresh_token = false;
            $OAuthSession->refresh_expires_in = Carbon::now()->addSeconds($oTokens->expires_in);
            $OAuthSession->provider_id = $this->provider->id;
            $OAuthSession->save();
            return $OAuthSession;
        }
        return false;
    }

    /*
    * isAuthValid
    * Checks if Authsession is still valid
    * required (int) OAuthSessionId
    * return (boolean) true = valid / false = invalid
    */
    public function isAuthValid($iAuthSessionId, $bReturnToken = false)
    {
        if($OAuthSession = OAuthSession::find($iAuthSessionId))
        {
            // Access token still valid
            if($OAuthSession->expires_in > Carbon::now())
            {
               return $bReturnToken ? $OAuthSession->access_token : true;
            }

            // Access token expired, check if we can refresh it
            elseif($OAuthSession->refresh_expires_in > Carbon::now())
            {
                // refresh the token
                $this->setProvider($OAuthSession->provider_id, true);
                $oTokens = $this->getTokens($OAuthSession->refresh_token, true);
                if(isset($oTokens->name)) $this->handleError($oTokens->name); // Nightbot error field

                // Save new tokens
                $OAuthSession->access_token = $oTokens->access_token;
                $OAuthSession->refresh_token = $oTokens->refresh_token;
                $OAuthSession->expires_in = Carbon::now()->addSeconds($oTokens->expires_in);
                $OAuthSession->refresh_expires_in = Carbon::now()->addSeconds(isset($oTokens->refresh_expires_in) ? $oTokens->refresh_expires_in : 5184000); // 60 Days default.
                $OAuthSession->save();
                return $bReturnToken ? $OAuthSession->access_token : true;
            }
        }
        return false;
    }

    /*
    * setProvider
    * Set the provider
    * required (int/string) Id or Name of provider
    * optional (boolean) if first parameter is an Id set this value true
    */
    public function setProvider($strService, $id = false)
    {
        $this->provider = $id === false ? OAuthProvider::where('name', $strService)->firstOrFail() :  OAuthProvider::findOrFail($strService);
    }

    private function handleError($strError)
    {
        Log::error($strError);
        switch($strError)
        {
            case 'access_denied':
                $strError = 'Authorization was denied by client';
            break;
            
            case 'invalid_client':
                $strError = 'Something went wrong';
            break;

            case 'invalid_grant':
                $strError = 'Authorization code expired/invalid, please authorize again';
            break;
            
            default:
                $strError = 'Something went wrong';
        }
        throw new Exception($strError);
    }
}
?>