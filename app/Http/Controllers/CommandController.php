<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

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
                if(isset($aAction['parser']))
                {
                    echo  $strMessage = empty($aQuery) ? "" : implode(" ", $aQuery);
                }
            }
            else return 'Invalid request';
        }
        else return 'Invalid request';
    }

    public function getAction($strAction)
    {
        $aActions = array(
            'add' => array('parser' => 'user'),
            'remove' => array('parser' => 'user'),
            'join' => array(),
            'leave' => array(),
            'position' => array('parser' => 'user'),
            'open' => array(),
            'close' => array()
        );

        if(isset($aActions[$strAction])) return $aActions[$strAction];
        return false;
    }
}
?>