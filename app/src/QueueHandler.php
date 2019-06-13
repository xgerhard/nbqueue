<?php

namespace App\src;

use App\src\Queue;
use Exception;
use DB;

class QueueHandler
{
    const ERR_NO_USER = 'No user info found';
    const ERR_DEFAULT = 'Something went wrong, please contact xgerhard';
    const ERR_NO_MOD = 'This command is only available for moderators';

    private $q = null;
    private $c = null;
    private $u = null;

    /**
     * Runs 'start' function on load, we need to have a channel and queue object to start
     *
    */
    public function __construct($aChannel)
    {
        if(!$this->start($aChannel))
        {
            return $this->returnText(self::ERR_DEFAULT);
            die;
        }
    }

    /**
     * Returns current queue info
     *
    */
    public function info()
    {
        $iCount = QueueUser::where([
            ['queue_id', '=', $this->c->active]
        ])->count();

        $strRes = 'Current queue: "'. $this->q->name .'", the queue is currently '. ($this->q->is_open == 1 ? 'open for "'. $this->getUserLevel($this->q->user_level) .'"' : 'closed') .' and contains '. $iCount .' user'. ($iCount == 1 ? '' : 's');

        // List available queues
        $aQueues = Queue::where([
            ['channel_id', '=', $this->c->id],
            ['id', '!=', $this->q->id]
        ])->get();

        if($aQueues && !$aQueues->isEmpty())
        {
            foreach($aQueues AS $oQueue)
            {
                $aQueueNames[] = $oQueue->name;
            }
            $strRes .= ". Available queues: [". implode(", ", $aQueueNames) ."]";
        }

        return $this->returnText($strRes);
    }

    /**
     * Returns url to full list of users in queue
     *
    */
    public function getList()
    {
        return $this->returnText('Full list of users and queues can be found here: '. url('list/'.  $this->c->id .'/'. urlencode($this->c->displayName)) .' ');
    }

    /**
     * Clears the current queue
     *
     * @return string
    */
    public function clearQueue($iQueueId = false)
    {
        if(!$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);

        DB::table('queue_users')->where('queue_id', '=', ($iQueueId === false ? $this->c->active : $iQueueId))->delete();
        return $this->returnText('Successfully cleared the queue'. $this->q->displayName);
    }

    /**
     * List next X persons from queue
     *
     * @return string
    */
    public function getListQueue($iLimit = 5)
    {
        if($iLimit == 0) $iLimit = 5;

        $aQueueUsers = QueueUser::where([
            ['queue_id', '=', $this->c->active]
        ])
        ->orderBy('created_at', 'asc')
        ->limit($iLimit === false ? 50 : (int) $iLimit)
        ->get();

        if(!$aQueueUsers || $aQueueUsers->isEmpty())
        {
            return $this->returnText('Unable to list queue'. ($this->q->name ? ' \''. $this->q->name .'\'' : '') .', queue is empty');
        }
        else
        {
            $aUsers = [];
            foreach($aQueueUsers AS $oQueueUser)
            {
                $aUsers[] = $oQueueUser->user->displayName;
            }
            return $this->returnText('Next '. (count($aUsers) > 1 ? count($aUsers) .' persons' : 'person') .' in the queue'. $this->q->displayName .': '. implode(", ", $aUsers));
        }
    }

    /**
     * Get next X person from queue
     *
     * @return string
    */
    public function getNext($iLimit = 1, $bRandom = false)
    {
        if(!$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);

        $iChars = 0;
        $iCharLimit = 150;
        $iLimit = (int) $iLimit;
        if($iLimit == 0) $iLimit = 1;
        if($iLimit > 10) $iLimit = 10; // Max

        $aQueueUsers = QueueUser::where([
            ['queue_id', '=', $this->c->active]
        ])
        ->orderByRaw($bRandom === false ? 'created_at' : 'RAND()', 'asc')
        ->limit($iLimit)
        ->get();

        if(!$aQueueUsers || $aQueueUsers->isEmpty())
        {
            return $this->returnText('Unable to get next from queue'. ($this->q->displayName ? ' '. $this->q->displayName : '') .', queue is empty');
        }
        else
        {
            $aUsers = [];
            foreach($aQueueUsers AS $oQueueUser)
            {
                $strTempRes = $oQueueUser->user->displayName . (trim($oQueueUser->message) == "" ? "" : ', with message: "'. $oQueueUser->message .'"');
                if(($iChars + strlen($strTempRes)) <= $iCharLimit)
                {
                    $aUsers[] = $strTempRes;
                    $iChars = $iChars + strlen($strTempRes);
                    $oQueueUser->forceDelete();
                }
            }
            return $this->returnText('Next '. (count($aUsers) > 1 ? count($aUsers) .' persons' : 'person') .' in the queue'. $this->q->displayName .': '. implode(", ", $aUsers));
        }
    }

    /**
     * Opens the current queue for users to join
     *
     * @return string
    */
    public function openQueue()
    {
        if(!$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);

        if($this->q->is_open == 0)
        {
            $this->q->is_open = 1;
            $this->q->save();
            return $this->returnText('Queue'. $this->q->displayName .' is now open');
        }
        else return $this->returnText('Queue'. $this->q->displayName .' is already open');
    }

    /**
     * Closes the current queue
     *
     * @return string
    */
    public function closeQueue()
    {
        if(!$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);

        if($this->q->is_open == 1)
        {
            $this->q->is_open = 0;
            $this->q->save();

            return $this->returnText('Queue'. $this->q->displayName .' is now closed');
        }
        else return $this->returnText('Queue'. $this->q->displayName .' is already closed');
    }

    /**
     * Get the position of the current user in the current queue
     *
     * @return string
    */
    public function getPosition($bFull = true)
    {
        if(!$this->u) return $this->returnText(RR_NO_USER);

        $qPosition = DB::select('
            SELECT
                FIND_IN_SET(
                    created_at,
                    (
                        SELECT GROUP_CONCAT(
                            created_at ORDER BY created_at ASC
                        )
                        FROM
                            queue_users
                        WHERE
                            queue_id = :quid
                    )
                ) AS position
            FROM
                queue_users
            WHERE
                queue_id = :qid
            AND
                user_id = :uid
            LIMIT 1
        ',
        ['qid' => $this->q->id, 'uid' => $this->u->id, 'quid' => $this->q->id]);

        if(isset($qPosition[0]))
        {
            return $bFull === true ? $this->returnText('Your current position in the queue'. $this->q->displayName .' is: '. $qPosition[0]->position) : $qPosition[0]->position;
        }
        else return $this->returnText('Cannot get position, you are not in the queue'. $this->q->displayName);
    }

    /**
     * Adds the current user to the current active queue
     *
     * @return string
    */
    public function joinQueue($strMessage)
    {
        if(!$this->q->is_open) return $this->returnText('The queue'. $this->q->displayName .' is currently closed');
        if(!$this->u) throw new Exception(self::ERR_NO_USER);
        if(!$this->isAllowed($this->getUserLevel($this->q->user_level))) return $this->returnText('The queue'. $this->q->displayName .' is currently only open for "'. $this->getUserLevel($this->q->user_level) .'s"');
        if(strlen($strMessage) > 50) return $this->returnText('Error: Max length of user message is 50');

        $oQueueUser = QueueUser::where([
            ['user_id', '=', $this->u->id],
            ['queue_id', '=', $this->c->active]
        ])->first();

        if($oQueueUser)
        {
            if($oQueueUser->message != $strMessage)
            {
                $oQueueUser->message = $strMessage;
                $oQueueUser->save();
                return $this->returnText('You are already in queue'. $this->q->displayName .' (position #'. $this->getPosition(false) .'), your queue message has been updated');
            }
            else return $this->returnText('You are already in queue'. $this->q->displayName .' (position #'. $this->getPosition(false) .')');
        }
        else
        {
            if($this->q->max_users != 0)
            {
                $iQueryUsers = QueueUser::where([
                    ['queue_id', '=', $this->c->active]
                ])->count();

                if($iQueryUsers >= $this->q->max_users) return $this->returnText('The queue'. $this->q->displayName .' is currently full, limit: '. $this->q->max_users);
            }

            $oQueueUser = QueueUser::create([
                'user_id' => $this->u->id,
                'queue_id' => $this->c->active,
                'message' => $strMessage
            ]);

            if($oQueueUser) return $this->returnText('Successfully added to queue'. $this->q->displayName . ', your position is #'. $this->getPosition(false));
        }
    }

    /**
     * Removes the current user to the current active queue
     *
     * @return string
    */
    public function leaveQueue()
    {
        if(!$this->u) return $this->returnText(self::ERR_NO_USER);

        $oQueueUser = QueueUser::where([
            ['user_id', '=', $this->u->id],
            ['queue_id', '=', $this->c->active]
        ])->first();

        if($oQueueUser)
        {
            $oQueueUser->forceDelete();
            return $this->returnText('Successfully removed from queue'. $this->q->displayName);
        }
        else
        {
            return $this->returnText('Unable to leave, you are not in queue'. $this->q->displayName);
        }
    }

    /**
     * Promotes a user to the first position of the current queue
     *
     * @return string
    */
    public function promoteUser($id = 0)
    {
        if(!$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);

        $id = (int) $id;
        $oQueueUser = QueueUser::find($id);
        if($oQueueUser)
        {
            if($oQueueUser->queue->channel_id == $this->c->id)
            {
                $oNextQueueUser = QueueUser::where([
                    ['queue_id', '=', $this->c->active]
                ])
                ->orderBy('created_at', 'asc')
                ->first();

                if($oNextQueueUser && $oNextQueueUser->id != $oQueueUser->id)
                {
                    $oQueueUser->created_at = strtotime($oNextQueueUser->created_at)-1;
                    $oQueueUser->save();
                    return $this->returnText('Successfully promoted '. $oQueueUser->user->displayName .' to first position of the queue');
                }
                else return $this->returnText('Unable to promote user in queue, user already first position in queue'); 
            }
            else return $this->returnText('Unable to promote user in queue, queue doesn\'t belong to this channel');
        }
        else return $this->returnText('Unable to promote user in queue, user not found');
    }

    /**
     * Sets the current channel and queue, returns true if succesfull
     * If a channel is not found yet, we add a default queue for them
     *
     * @return boolean
    */
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
                $oQueue = $this->addQueue('default', 1);
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

        if($this->q && $this->c)
        {
            $this->q->displayName = $this->q->name == 'default' ? '' : ' "'. ucfirst($this->q->name) .'"';

            $oChannel->name = $aChannel['name'];
            $oChannel->displayName = $aChannel['displayName'];
            return true;
        }
        return false;
    }

    /**
     * Sets the current user
     * If user doesn't exist yet, we add them to our database
     *
    */
    public function setUser($aUser)
    {
        $oUser = User::where([
            ['provider_id', '=', $aUser['providerId']],
            ['provider', '=', $aUser['provider']]
        ])->first();

        if(!$oUser)
        {
            $oUser = User::create([
                'provider' => $aUser['provider'],
                'provider_id' => $aUser['providerId'],
                'name' => $aUser['name'],
                'displayName' => $aUser['displayName'],
            ]);
        }
        else
        {
            if($oUser->name != $aUser['name'] || $oUser->displayName != $aUser['displayName'])
            {
                $oUser->name = $aUser['name'];
                $oUser->displayName = $aUser['displayName'];
                $oUser->save();
            }
        }

        $oUser->userLevel = $aUser['userLevel'];
        $this->u = $oUser;
    }

    /**
     * Adds a queue for a channel, return the new queue on success
     *
     * @return object | boolean
    */
    public function addQueue($strName, $iOpen = 0, $bText = false)
    {
        if($bText && !$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);
        if(trim($strName) == "") return $this->returnText('No queue name specified to add');

        $oQueue = Queue::where([
            ['channel_id', '=', $this->c->id],
            ['name', '=', $strName]
        ])->first();

        if(!$oQueue)
        {
            $oQueue = Queue::create([
                'channel_id' => $this->c->id,
                'name' => $strName,
                'is_open' => ($iOpen == 0 ? 0 : 1)
            ]);
        }
        elseif($bText) return $this->returnText('Queue "'. $strName .'" already exists');

        if($bText === false)
        {
            if($oQueue) return $oQueue;
            return false;
        }
        elseif($oQueue)
        {
            return $this->returnText('Successfully added queue "'. $strName .'"');
        }
    }

    /**
     * Deletes a queue for a channel, clears the queue before deleting
     *
     * @return string
    */
    public function deleteQueue($strName)
    {
        if(!$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);
        if(trim($strName) == "") return $this->returnText('No queue name specified to delete');
        if(strtolower(trim($strName)) == 'default') return $this->returnText('Cannot delete the \'default\' queue');

        $oQueue = Queue::where([
            ['channel_id', '=', $this->c->id],
            ['name', '=', $strName]
        ])->first();

        if($oQueue)
        {
            if($this->c->active == $oQueue->id)
            {
                // were deleting the current active queue so we have to set the default queue active again
                $oDefaultQueue = Queue::where([
                    ['channel_id', '=', $this->c->id],
                    ['name', '=', 'default']
                ])->first();

                if($oDefaultQueue)
                {
                    $this->c->active = $oDefaultQueue->id;
                    $this->c->save();
                }
            }

            $this->clearQueue($oQueue->id);
            $oQueue->forceDelete();
            return $this->returnText('Successfully deleted queue "'. $strName.'"');
        }
        else
        {
            return $this->returnText('Unable to delete queue "'. $strName .'", queue doesn\'t exist');
        }
    }

    /**
     * Sets a queue as active queue
     *
     * @return string
    */
    public function setQueue($strName)
    {
        if(!$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);
        if(trim($strName) == "") return $this->returnText('No queue name specified to set active');

        $oQueue = Queue::where([
            ['channel_id', '=', $this->c->id],
            ['name', '=', $strName]
        ])->first();

        if($oQueue)
        {
            $this->c->active = $oQueue->id;
            $this->c->save();
            return $this->returnText('Queue "'. $strName .'" is now the active queue');
        }
        else
        {
            return $this->returnText('Unable to set queue "'. $strName .'", queue doesn\'t exist'); 
        }
    }

    /**
     * Remove user from queue by QueueUserId
     *
     * @return string
    */
    public function removeQueueUser($id = 0)
    {
        $id = (int) $id;
        $oQueueUser = QueueUser::find($id);
        if($oQueueUser)
        {
            if($oQueueUser->queue->channel_id == $this->c->id)
            {
                $oQueueUser->forceDelete();
                return $this->returnText('Successfully removed user from queue');
            }
            else return $this->returnText('Unable to remove from queue, queue doesn\'t belong to this channel');
        }
        else return $this->returnText('Unable to remove from queue, user not found');
    }

    /**
     * Set limit of the current queue
     *
     * @return string
    */
    public function setQueueLimit($strLimit)
    {
        if(!$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);
        if(trim($strLimit) == '') return $this->returnText('No queue limit provided');

        $iLimit = (int) $strLimit;
        if($iLimit > 9999)
            return $this->returnText('Invalid queue limit provided, number must be less than 9999');
        else
        {
            $this->q->max_users = $iLimit;
            $this->q->save();
            return $this->returnText('Successfully set the queue limit to: '. ($iLimit == 0 ? 'unlimited (0)' : $iLimit));
        }
    }

    /**
     * Adds username to text response, makes sure the character limit is not passed.
     *
     * @return string
    */
    public function returnText($strMessage)
    {
        $strReturnMessage = "";
        if($this->u) $strReturnMessage .= '@'. $this->u->displayName .': ';
        return substr($strReturnMessage . $strMessage .'.', 0, 200);
    }

    /**
     * Sets the userLevel of the currenct queue
     *
     * @return string
    */
    public function setUserLevel($strUserLevel)
    {
        if(!$this->isAllowed('moderator')) return $this->returnText(self::ERR_NO_MOD);
        $strUserLevel = trim(strtolower($strUserLevel));
        if($iUserLevel = $this->getUserLevel($strUserLevel, true))
        {
            if($this->q->user_level != $iUserLevel)
            {
                $this->q->user_level = $iUserLevel;
                $this->q->save();
                return $this->returnText('Successfully set the UserLevel to "'. $strUserLevel .'"');
            }
            else return $this->returnText('The current queue is already set to UserLevel "'. $strUserLevel .'"');
        }
        else return $this->returnText('Invalid UserLevel. Available UserLevels: moderator, regular, subscriber, vip, everyone');
    }

    /**
     * Checks if the user is allowed to perform a certain action
     *
     * @return boolean
    */
    private function isAllowed($strUserLevel)
    {
        if($this->u)
        {
            $aAllowed = ['owner'];
            switch($strUserLevel)
            {
                case 'everyone':
                    return true;
                break;
     
                case 'moderator':
                    $aAllowed = ['owner', 'moderator'];
                break;

                case 'vip':
                    $aAllowed = ['owner', 'moderator', 'twitch_vip'];
                break;

                case 'regular':
                    $aAllowed = ['owner', 'moderator', 'twitch_vip', 'regular'];
                break;

                case 'subscriber':
                    $aAllowed = ['owner', 'moderator', 'twitch_vip', 'regular', 'subscriber'];
                break;
            }
            return in_array($this->u->userLevel, $aAllowed);
        }
        return false;
    }

    /**
     * Matches the int userLevel to string userLevel or vice versa
     * returns false if invalid userLevel
     *
     * @return int / string
    */
    private function getUserLevel($xUserLevel, $bReverse = false)
    {
        $aUserLevels = [
            6 => 'owner',
            5 => 'moderator',
            4 => 'regular',
            3 => 'subscriber',
            2 => 'vip',
            1 => 'everyone'
        ];

        if($bReverse)
            return array_search($xUserLevel, $aUserLevels);

        elseif(isset($aUserLevels[$xUserLevel]))
            return $aUserLevels[$xUserLevel];

        return false;
    }
}
?>