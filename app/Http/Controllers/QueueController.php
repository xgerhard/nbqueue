<?php
namespace App\Http\Controllers;

use App\src\Channel;
use Laravel\Lumen\Routing\Controller as BaseController;

class QueueController extends BaseController
{
    public function list($channelId, $channelName = false)
    {
        $channelId = (int) $channelId;
        if($channelId && is_int($channelId))
        {
            $oChannel = Channel::with([
                'queues' => function ($q) {
                    $q->orderBy('created_at', 'ASC');
                },
                'queues.queueUsers' => function ($qu) {
                    $qu->orderBy('created_at', 'ASC');
                },
                'queues.queueUsers.user'
            ])
            ->findOrFail($channelId);

            return view('list', ['channel' => $oChannel]);

            /* Usage
            $oChannel->channelOwner()->displayName;
            foreach($oChannel->queues as $oQueue)
            {
                echo $oQueue->name .'<br/><ul>';
                foreach($oQueue->queueUsers as $oQueueUser)
                {
                    echo '<li>'. $oQueueUser->user->displayName .'</li>';
                }
                echo '</ul>';
            }*/
        }
        else echo 'invalid channelId';
    }
}