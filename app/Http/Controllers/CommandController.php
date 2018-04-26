<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\src\QueueHandler;

class CommandController extends BaseController
{
    public function QueryParser(Request $request)
    {
        if($request->has('q'))
        {
            $aQuery = explode(" ", trim($request->input('q')));
            if(!empty($aQuery) && $strAction = $this->getAction($aQuery[0]))
            {
                array_shift($aQuery);
                $strMessage = empty($aQuery) ? "" : implode(" ", $aQuery);

                // Set these headers as test for now
                parse_str('name=xgerhard&displayName=xgerhard&provider=twitch&providerId=00000001', $aChannel);
                parse_str('name=xgerhard2&displayName=xgerhard2&provider=twitch&providerId=00000002&userLevel=owner', $aUser);

                try{
                    $oQH = new QueueHandler($aChannel);
                    $oQH->setUser($aUser);

                    switch($strAction)
                    {
                        case 'join':
                            return $oQH->joinQueue($strMessage);
                        break;

                        case 'leave':
                            return $oQH->leaveQueue();
                        break;
                        
                        case 'position':
                            return $oQH->getPosition();
                        break;

                        case 'open':
                            return $oQH->openQueue();
                        break;

                        case 'close':
                            return $oQH->closeQueue();
                        break;

                        case 'next':
                            return $oQH->getNextPerson();
                        break;

                        case 'next5':
                            return $oQH->getListQueue(5);
                        break;

                        case 'clear':
                            return $oQH->clearQueue();
                        break;

                        case 'delq':
                            return $oQH->deleteQueue($strMessage);
                        break;

                        case 'delq':
                            return $oQH->deleteQueue($strMessage);
                        break;
                    }
                }
                //catch(\Exception $e)
                catch(Exception $e)
                {
                    dd($e);
                }
            }
            else return 'Invalid action, available actions: add, remove, join, leave, position';
        }
        else return 'Invalid request';
    }

    public function getAction($strAction)
    {
        $strAction = strtolower(trim($strAction));
        $aActions = array(
            'add',
            'remove',
            'join' ,
            'leave',
            'position',
            'open',
            'close',
            'next',
            'next5',
            'clear'
        );
        if(in_array($strAction, $aActions)) return $strAction;
        return false;
    }
}
?>