<?php
namespace App\src;

use GuzzleHttp\Client;
use Exception;

class NightbotAPI
{
    public $baseUrl = 'https://api.nightbot.tv/1/';
    private $accessToken;

    public function __construct($strAccessToken)
    {
        $this->accessToken = $strAccessToken;
    }

    public function getCustomCommands()
    {
        return $this->request('commands')->commands;
    }

    public function addCustomCommand($strName, $strMessage, $strUserLevel = 'everyone', $iCooldown = 5, $strAlias = "")
    {
        return $this->request('commands', 'POST', [], [
            'coolDown' => $iCooldown,
            'userLevel' => $strUserLevel,
            'name' => $strName,
            'message' => $strMessage,
            'alias' => $strAlias
        ]);
    }

    public function editCustomCommand($strCommandId, $strName, $strMessage, $strUserLevel = 'everyone', $iCooldown = 5, $strAlias = "")
    {
        return $this->request('commands/'. $strCommandId, 'PUT', [], [
            'coolDown' => $iCooldown,
            'userLevel' => $strUserLevel,
            'name' => $strName,
            'message' => $strMessage,
            'alias' => $strAlias
        ]);
    }

    public function request($strUrl, $strMethod = 'GET', $aPostHeaders = [], $aPost = [])
    {
        $strUrl = $this->baseUrl . $strUrl;
        $aHeaders = ['Authorization' => 'Bearer '. $this->accessToken, 'Accept' => 'application/json'];
        $aRequestData = [];

        if(!empty($aHeaders)) $aHeaders = array_merge($aHeaders, $aPostHeaders);
        if(!empty($aHeaders)) $aRequestData['headers'] = $aHeaders;
        if(!empty($aPost)) $aRequestData['form_params'] = $aPost;

        $oClient = new Client([
            'http_errors' => false, 
            // 'verify' => false - for localhost
        ]);

        $oResponse = $oClient->request($strMethod, $strUrl, $aRequestData);
        $oResponseBody = json_decode($oResponse->getBody()->getContents());

        if(isset($oResponseBody->status) && $oResponseBody->status == 200)
            return $oResponseBody;
        else
            throw new Exception('Something went wrong..');
    }
}
?>