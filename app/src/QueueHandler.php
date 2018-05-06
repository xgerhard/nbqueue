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
        if(!$this->start($aChannel)) throw new Exception(ERR_DEFAULT);
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
        return 'Current queue: \''. $this->q->name .'\', the queue is currently '. ($this->q->is_open == 1 ? 'open' : 'closed') .' and contains '. $iCount .' user'. ($iCount == 1 ? '' : 's') .'.';
    }

    /**
     * Clears the current queue
     *
     * @return string
    */
    public function clearQueue($iQueueId = false)
    {
        if(!$this->u->isModerator) return ERR_NO_MOD;

        DB::table('queue_users')->where('queue_id', '=', ($iQueueId === false ? $this->c->active : $iQueueId))->delete();
        return 'Succesfully cleared the queue'. $this->q->displayName;
    }

    /**
     * List next X persons from queue
     *
     * @return string
    */
    public function getListQueue($iLimit = 5)
    {
        $aQueueUsers = QueueUser::where([
            ['queue_id', '=', $this->c->active]
        ])
        ->orderBy('created_at', 'asc')
        ->limit($iLimit === false ? 1000 : (int) $iLimit)
        ->get();

        if(!$aQueueUsers || $aQueueUsers->isEmpty())
        {
            return 'Unable to list queue'. ($this->q->name ? ' \''. $this->q->name .'\'' : '') .', queue is empty';
        }
        else
        {
            $aUsers = [];
            foreach($aQueueUsers AS $oQueueUser)
            {
                $aUsers[] = $oQueueUser->user->displayName;
            }
            return 'Next '. (count($aUsers) > 1 ? count($aUsers) .' persons' : 'person') .' in the queue'. $this->q->displayName .': '. implode(", ", $aUsers);
        }
    }

    /**
     * Get next X person from queue
     *
     * @return string
    */
    public function getNext($iLimit = 1)
    {
        if(!$this->u->isModerator) return ERR_NO_MOD;

        $iChars = 0;
        $iCharLimit = 150;
        $iLimit = (int) $iLimit;
        if($iLimit == 0) $iLimit = 1;
        if($iLimit > 10) $iLimit = 10; // Max

        $aQueueUsers = QueueUser::where([
            ['queue_id', '=', $this->c->active]
        ])
        ->orderBy('created_at', 'asc')
        ->limit($iLimit)
        ->get();

        if(!$aQueueUsers || $aQueueUsers->isEmpty())
        {
            return 'Unable to list queue'. ($this->q->displayName ? ' \''. $this->q->displayName .'\'' : '') .', queue is empty';
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
            return 'Next '. (count($aUsers) > 1 ? count($aUsers) .' persons' : 'person') .' in the queue'. $this->q->displayName .': '. implode(", ", $aUsers);
        }
    }

    /**
     * Opens the current queue for users to join
     *
     * @return string
    */
    public function openQueue()
    {
        if(!$this->u->isModerator) return ERR_NO_MOD;

        if($this->q->is_open == 0)
        {
            $this->q->is_open = 1;
            $this->q->save();
            return 'Queue'. $this->q->displayName .' is now open';
        }
        else return 'Queue'. $this->q->displayName .' is already open';
    }

    /**
     * Closes the current queue
     *
     * @return string
    */
    public function closeQueue()
    {
        if(!$this->u->isModerator) return ERR_NO_MOD;

        if($this->q->is_open == 1)
        {
            $this->q->is_open = 0;
            $this->q->save();

            return 'Queue'. $this->q->displayName .' is now closed';
        }
        else return 'Queue'. $this->q->displayName .' is already closed';
    }

    /**
     * Get the position of the current user in the current queue
     *
     * @return string
    */
    public function getPosition($bFull = true)
    {
        if(!$this->u) throw new Exception(ERR_NO_USER);

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
                        AND
                            user_id = :usid
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
        ['qid' => $this->q->id, 'uid' => $this->u->id, 'quid' => $this->q->id, 'usid' => $this->u->id]);

        if(isset($qPosition[0]))
        {
            return ($bFull === true ? 'Your current position in the queue'. $this->q->displayName .' is: ' : '') . $qPosition[0]->position;
        }
        else return 'Cannot get position, you are not in the queue'. $this->q->displayName;
    }

    /**
     * Adds the current user to the current active queue
     *
     * @return string
    */
    public function joinQueue($strMessage)
    {
        if(!$this->q->is_open) return 'The queue'. $this->q->displayName .' is currently closed';
        if(!$this->u) throw new Exception(ERR_NO_USER);
        if(strlen($strMessage) > 50) return 'Error: Max length of user message is 50';

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
                return 'You are already in queue'. $this->q->displayName .' (#'. $this->getPosition(false) .'), your queue message has been updated';
            }
            else return 'You are already in queue'. $this->q->displayName .' (#'. $this->getPosition(false) .')';
        }
        else
        {
            $oQueueUser = QueueUser::create([
                'user_id' => $this->u->id,
                'queue_id' => $this->c->active,
                'message' => $strMessage
            ]);

            if($oQueueUser) return 'Succesfully added to queue'. $this->q->displayName;
        }
    }

    /**
     * Removes the current user to the current active queue
     *
     * @return string
    */
    public function leaveQueue()
    {
        if(!$this->u) throw new Exception(ERR_NO_USER);

        $oQueueUser = QueueUser::where([
            ['user_id', '=', $this->u->id],
            ['queue_id', '=', $this->c->active]
        ])->first();

        if($oQueueUser)
        {
            $oQueueUser->forceDelete();
            return 'Succesfully removed from queue'. $this->q->displayName;
        }
        else
        {
            return 'Unable to leave, you are not in queue'. $this->q->displayName;
        }
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

        $oUser->isModerator = ($aUser['userLevel'] == 'owner' || $aUser['userLevel'] == 'moderator');
        $this->u = $oUser;
    }

    /**
     * Adds a queue for a channel, return the new queue on success
     *
     * @return object | boolean
    */
    public function addQueue($strName, $iOpen = 0, $bText = false)
    {
        if(!$this->u->isModerator) return ERR_NO_MOD;
        if(trim($strName) == "") return 'No queue name specified to add';

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
        elseif($bText) return 'Queue "'. $strName .'" already exists';

        if($bText === false)
        {
            if($oQueue) return $oQueue;
            return false;
        }
        elseif($oQueue)
        {
            return 'Succesfully added queue "'. $strName .'"';
        }
    }

    /**
     * Deletes a queue for a channel, clears the queue before deleting
     *
     * @return string
    */
    public function deleteQueue($strName)
    {
        if(!$this->u->isModerator) return ERR_NO_MOD;
        if(trim($strName) == "") return 'No queue name specified to delete';
        if(strtolower(trim($strName)) == 'default') return 'Cannot delete the \'default\' queue';

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
            return 'Succesfully deleted queue "'. $strName.'"';
        }
        else
        {
            return 'Unable to delete queue "'. $strName .'", queue doesn\'t exist';
        }
    }

    public function setQueue($strName)
    {
        if(!$this->u->isModerator) return ERR_NO_MOD;
        if(trim($strName) == "") return 'No queue name specified to set active';

        $oQueue = Queue::where([
            ['channel_id', '=', $this->c->id],
            ['name', '=', $strName]
        ])->first();

        if($oQueue)
        {
            $this->c->active = $oQueue->id;
            $this->c->save();
            return 'Queue "'. $strName .'" is now the active queue';
        }
        else
        {
            return 'Unable to set queue "'. $strName .'", queue doesn\'t exist'; 
        }
    }
}
?>