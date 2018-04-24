<?php

namespace App\src;

use App\src\Queue;

class QueueHandler
{
    private $q = null;
    private $c = null;

    public function __construct($aChannel)
    {
        if(!$this->start($aChannel)) return 'Something went wrong, please contact xgerhard';
    }

    private function start($aChannel)
    {
        $oChannel = Channel::where([
            ['provider', '=', $aChannel['provider']],
            ['provider_id', '=', $aChannel['providerId']]
        ])->first();

        if(!$oChannel)
        {
            $oChannel = Channel::create([
                'provider' => $aChannel['provider'],
                'provider_id' => $aChannel['providerId']
            ]);

            if($oChannel)
            {
                $this->c = $oChannel;
                $oQueue = $this->addQueue('default');
                if($oQueue)
                {
                    $oChannel->active = $oQueue->id;
                    $oChannel->save();
                    $this->q = $oQueue;
                }
            }
        }
        else
        {
            $this->c = $oChannel;
            $oQueue = Queue::find($oChannel->active);
            if($oQueue) $this->q = $oQueue;
        }

        if($this->q && $this->c) return true;
        return false;
    }

    public function addQueue($strName)
    {
        $oQueue = Queue::create([
            'id' => $this->c->id,
            'name' => $strName
        ]);

        if($oQueue) return $oQueue;
        return false;
    }
}
?>