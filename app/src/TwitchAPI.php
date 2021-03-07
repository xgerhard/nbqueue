<?php
namespace App\src;

use GuzzleHttp\Client;
use Exception;
use Log;
use App\OAuth\OAuthProvider;
use App\OAuth\OAuthHandler;
use Carbon\Carbon;

class TwitchAPI
{
    public $baseUrl = 'https://api.twitch.tv/helix/';
    private $accessToken;
    private $clientId;

    public function __construct()
    {
        $oTwitch = OAuthProvider::where('name', 'twitch')->first();
        if($oTwitch)
        {
            $oSession = $oTwitch->lastSession();
            if(!$oSession || Carbon::now()->gt($oSession->expires_in))
            {
                $oAuthHandler = new OAuthHandler('twitch');
                $oSession = $oAuthHandler->getAppTokens();
                if(!$oSession)
                {
                    Log::error('Failed to get app access token');
                    throw new Exception('Something went wrong, please contact xgerhard');
                }
            }

            $this->accessToken = $oSession->access_token;
            $this->clientId = $oTwitch->client_id;
        }
    }

    public function getUsers($user, $id = false)
    {
        return $this->request('users?'. ($id == true ? 'id' : 'login') .'='. urlencode(is_array($user) ? implode(',', $user) : $user))->data;
    }

    public function request($strUrl, $strMethod = 'GET', $aPost = [])
    {
        $strUrl = $this->baseUrl . $strUrl;
        $aHeaders = [
            'Authorization' => 'Bearer '. $this->accessToken,
            'client-id' => $this->clientId,
            'Accept' => 'application/json'
        ];
        $aRequestData = [];

        if(!empty($aHeaders)) $aRequestData['headers'] = $aHeaders;
        if(!empty($aPost)) $aRequestData['form_params'] = $aPost;

        $oClient = new Client([
            'http_errors' => false, 
            //'verify' => false
        ]);

        $oResponse = $oClient->request($strMethod, $strUrl, $aRequestData);
        $oResponseBody = json_decode($oResponse->getBody()->getContents());

        if(!isset($oResponseBody->status) || $oResponseBody->status == 200)
            return $oResponseBody;
        else
        {
            if(isset($oResponseBody->message)) Log::error($oResponseBody->message);
            throw new Exception('Something went wrong..');
        }
    }
}
?>