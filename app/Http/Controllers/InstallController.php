<?php
namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\OAuth\OAuthHandler;
use App\src\Session;
use App\src\NightbotAPI;
use Illuminate\Http\Request;

class InstallController extends BaseController
{
    public function startAuto()
    {
        if(!$this->isLoggedIn()) return $this->renderLogin();
        return view('install', ['commands' => $this->getQueueCommands()]);
    }

    public function installAuto(Request $request)
    {
        $bSuccess = false;
        $aErrors = [];
        $aCommandNames = [];

        $strAccessToken = $this->isLoggedIn();
        if(!$strAccessToken) return $this->renderLogin();

        $aCommands = $this->getQueueCommands();

        if($request->input('command') !== null && is_array($request->input('command')) && !empty($request->input('command')))
        {
            foreach($request->input('command') as $strCommandKey => $aCommand)
            {
                if(isset($aCommand['enable']) && $aCommand['enable'] == 'on')
                {
                    if(!isset($aCommand['name']) || trim($aCommand['name']) == "")
                    {
                        $aErrors[] = 'Command code can\'t be empty.';
                        break;
                    }
                    else
                    {
                        if(!isset($aCommands[$strCommandKey]))
                        {
                            $aErrors[] = 'Unknown command: \''. $strCommandKey .'\'';
                            break;
                        }
                        else
                        {
                            if(in_array(trim($aCommand['name']), $aCommandNames))
                            {
                                $aErrors[] = 'All commands must have different command codes.';
                                break;
                            }
                            else
                                $aCommandNames[$strCommandKey] = trim($aCommand['name']);
                        }
                    }
                }
            }
        }

        if(empty($aCommandNames))
        {
            $aErrors[] = 'No command selected to add.';
        }

        if(empty($aErrors))
        {
            $oNightbotAPI = new NightbotApi($strAccessToken);
            $aCustomCommands = $oNightbotAPI->getCustomCommands();

            $aUserCommands = [];
            if(!empty($aCustomCommands))
            {
                foreach($aCustomCommands AS $oCustomCommand)
                {
                    $aUserCommands[$oCustomCommand->name] = $oCustomCommand->_id;
                }
            }

            foreach($aCommands AS $strCommandCode => $aCommand)
            {
                if(isset($aCommandNames[$strCommandCode]) || isset($aCommand['main_command']))
                {
                    $strCommandCode = isset($aCommandNames[$strCommandCode]) ? $aCommandNames[$strCommandCode] : $aCommand['name'];
                    $strUserLevel = isset($aCommand['mod']) ? 'moderator' : 'everyone';
                    $strAlias = isset($aCommand['main_command']) ? '' : '!q';

                    if(isset($aUserCommands[$strCommandCode]))
                    {
                        $oNightbotAPI->editCustomCommand($aUserCommands[$strCommandCode], $strCommandCode, $aCommand['code'], $strUserLevel, 5, $strAlias);
                    }
                    else
                    {
                        $oNightbotAPI->addCustomCommand($strCommandCode, $aCommand['code'], $strUserLevel, 5, $strAlias);
                    }
                }
            }
            $bSuccess = true;
        }
        return view('install', ['commands' => $this->getQueueCommands(), 'errors' => $aErrors, 'success' => $bSuccess]);
    }

    public function isLoggedIn()
    {
        $OAuthHandler = new OAuthHandler('Nightbot');
        if(Session::has('Nightbot-auth') && $login = $OAuthHandler->isAuthValid(Session::pull('Nightbot-auth'), true))
        {
            return $login;
        }
        return false;
    }

    public function renderLogin()
    {
        $OAuthHandler = new OAuthHandler('Nightbot');
        return view('install', ['auth_url' => $OAuthHandler->getAuthUrl()]);
    }

    public function getQueueCommands()
    {
        return [
            'q' => [
                'name' => '!q',
                'description' => 'This command handles all functionalities of the queue system, it must be installed for the queue system to work.',
                'main_command' => true,
                'code' => '$(urlfetch https://2g.be/twitch/nbqueue/public/?q=$(querystring))'
            ],
            'join' => [
                'name' => '!join',
                'description' => 'Join the current queue. Optional message. Usage: "!join" or "!join message".',
                'code' => 'join $(query)'
            ],
            'leave' => [
                'name' => '!leave',
                'description' => 'Leave the current queue. Usage: "!leave".',
                'code' => 'leave'
            ],
            'position' => [
                'name' => '!position',
                'description' => 'Returns the position of the user in the current queue. Usage: "!position".',
                'code' => 'position'
            ],
            'list' => [
                'name' => '!list',
                'description' => 'Returns an url with a full list of all users in the queues. Usage: "!list".',
                'code' => 'list'
            ],
            'info' => [
                'name' => '!info',
                'description' => 'Returns information (name, amount of users, status) of the current queue. Usage: "!info".',
                'code' => 'info'
            ],
            'open' => [
                'name' => '!open',
                'description' => 'Opens the current queue. Usage: "!open".',
                'code' => 'open',
                'mod' => true
            ],
            'close' => [
                'name' => '!close',
                'description' => 'Closes the current queue. Usage: "!close".',
                'code' => 'close',
                'mod' => true
            ],
            'next' => [
                'name' => '!next',
                'description' => 'Picks the next user. Optional number for multiple users. Usage: "!next" or "!next 3".',
                'code' => 'next $(query)',
                'mod' => true
            ],
            'random' => [
                'name' => '!random',
                'description' => 'Picks a random user. Optional number for multiple users. Usage: "!random" or "!random 3".',
                'code' => 'random $(query)',
                'mod' => true
            ],
            'clear' => [
                'name' => '!clear',
                'description' => 'Clears the current queue. Usage: "!clear".',
                'code' => 'clear',
                'mod' => true
            ],
            'add' => [
                'name' => '!add',
                'description' => 'Adds a subqueue. Required message. Usage example: "!add Fortnite".',
                'code' => 'add $(query)',
                'mod' => true
            ],
            'del' => [
                'name' => '!del',
                'description' => 'Deletes a subqueue. Required message. Usage example: "!del Fortnite".',
                'code' => 'del $(query)',
                'mod' => true
            ],
            'set' => [
                'name' => '!set',
                'description' => 'Sets a subqueue as active queue. Required message. Usage example: "!set Fortnite".',
                'code' => 'set $(query)',
                'mod' => true
            ],
            'remove' => [
                'name' => '!remove',
                'description' => 'Deletes user from queue by ID, this ID can be found when opening the full list in browser (list command). Usage example: "!remove 1".',
                'code' => 'remove $(query)',
                'mod' => true
            ],
            'userlevel' => [
                'name' => '!userlevel',
                'description' => 'Sets the UserLevel of the current active queue. Available userlevels: moderator, vip, regular, subscriber, everyone. Usage example: "!userlevel suscriber".',
                'code' => 'userlevel $(query)',
                'mod' => true
            ],
            'promote' => [
                'name' => '!promote',
                'description' => 'Promotes user to first position of queue by ID, this ID can be found when opening the full list in browser (list command). Usage example: "!promote 1".',
                'code' => 'promote $(query)',
                'mod' => true
            ]
        ];
    }
}
?>
