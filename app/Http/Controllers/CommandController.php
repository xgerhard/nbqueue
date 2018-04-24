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
            if(!empty($aQuery) && $aAction = $this->getAction($aQuery[0]))
            {
                array_shift($aQuery);
                $strMessage = empty($aQuery) ? "" : implode(" ", $aQuery);
                
                // Set these headers as test for now
                parse_str('name=xgerhard&displayName=xgerhard&provider=twitch&providerId=00000001', $aChannel);
                parse_str('name=xgerhard&displayName=xgerhard&provider=twitch&providerId=00000001&userLevel=owner', $aUser);

                $oQH = new QueueHandler($aChannel);
            }
            else return 'Invalid action, available actions: add, remove, join, leave, position';
        }
        else return 'Invalid request';
    }

    public function getAction($strAction)
    {
        $aActions = array(
            'add',
            'remove',
            'join' ,
            'leave',
            'position',
            'open',
            'close'
        );
        if(in_array($strAction, $aActions)) return true;
        return false;
    }
}
?>