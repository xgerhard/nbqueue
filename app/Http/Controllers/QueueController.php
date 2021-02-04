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
                'channelOwner',
                'queues' => function ($q) {
                    $q->orderBy('created_at', 'ASC');
                },
                'queues.queueUsers' => function ($qu) {
                    $qu->orderBy('created_at', 'ASC');
                },
                'queues.queueUsers.user'
            ])
            ->findOrFail($channelId);

            if(!$oChannel->channelOwner)
                return 'Woops, this page has been updated. <br/><br/>Please run the !q command once in your channel (or the channel you are watching), for example "!q list". <br/><br/>Refresh this page after, if this error is not resolved, please contact xgerhard on Twitch or @gerhardoh on Twitter. <br/><br/>Sorry for the inconvenience :)';
            else
                return view('list', ['channel' => $oChannel]);
        }
        else return 'Invalid channelId';
    }
}