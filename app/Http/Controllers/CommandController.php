<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\src\QueueHandler;
use xgerhard\nbheaders\nbheaders;
use Log;

class CommandController extends Controller
{
    public function QueryParser(Request $request)
    {
        if($request->has('q'))
        {
            $aQuery = explode(' ', trim($request->input('q')));
            $strAction = !empty($aQuery) ? trim(strtolower(array_shift($aQuery))) : 'default';

            $strMessage = empty($aQuery) ? '' : urldecode(implode(' ', $aQuery));
            $oNbHeaders = new nbheaders();

            // If APP_DEBUG is set to true in your .env, you can set a test user & channel here, so the script works from the browser.
            // This will manually set the request headers that are normally send by Nightbot urlFetch: https://docs.nightbot.tv/commands/variables/urlfetch
            if(env('APP_DEBUG'))
            {
                $oNbHeaders->setUser([
                    'name' => 'xgerhard',
                    'displayName' => 'xgerhard',
                    'provider' => 'twitch',
                    'providerId' => '12345678',
                    'userLevel' => 'owner'
                ]);
                $oNbHeaders->setChannel([
                    'name' => 'xgerhard',
                    'displayName' => 'xgerhard',
                    'provider' => 'twitch',
                    'providerId' => '12345678'
                ]);
            }

            if(!$oNbHeaders->isNightbotRequest())
            {
                return 'This command only works through Nightbot.';
            }

            try
            {
                $oQH = new QueueHandler($oNbHeaders->getChannel());
                if($oNbHeaders->getUser()) $oQH->setUser($oNbHeaders->getUser());

                switch($strAction)
                {
                    case 'adduser':
                        return $oQH->addUser($strMessage);
                    break;

                    case 'join':
                        return $oQH->joinQueue($strMessage);
                    break;

                    case 'leave':
                        return $oQH->leaveQueue();
                    break;

                    case 'position':
                        return $oQH->getPosition();
                    break;

                    case 'list':
                        return $oQH->getList();
                    break;

                    case 'info':
                        return $oQH->info();
                    break;

                    case 'open':
                        return $oQH->openQueue();
                    break;

                    case 'close':
                        return $oQH->closeQueue();
                    break;

                    case 'next':
                        return $oQH->getNext($strMessage, false);
                    break;

                    case 'random':
                        return $oQH->getNext($strMessage, true);
                    break;

                    case 'who':
                        return $oQH->getListQueue((int) $strMessage);
                    break;

                    case 'clear':
                        return $oQH->clearQueue();
                    break;

                    case 'add':
                        return $oQH->addQueue($strMessage, 1, true);
                    break;

                    case 'del':
                        return $oQH->deleteQueue($strMessage);
                    break;

                    case 'set':
                        return $oQH->setQueue($strMessage);
                    break;

                    case 'userlevel':
                    case 'ul':
                        return $oQH->setUserLevel($strMessage);
                    break;

                    case 'remove':
                        return $oQH->removeQueueUser($strMessage);
                    break;

                    case 'promote':
                        return $oQH->promoteUser((int) $strMessage);
                    break;

                    case 'setlimit':
                        return $oQH->setQueueLimit($strMessage);
                    break;

                    case 'help':
                        return 'See '. secure_url('/docs') .' for usage info';
                    break;

                    default:
                        return 'Invalid action, available actions: add, remove, join, leave, position. See '. secure_url('/docs') .' for full list of commands';
                    break;
                }
            }
            catch(Exception $e)
            {
                Log::error($e);
                return 'Something went wrong..';
            }
        }
        else return redirect('/docs');
    }
}
?>