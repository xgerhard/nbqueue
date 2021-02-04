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

    private $channel = null;
    private $user = null;

    /**
     * Runs 'start' function on load, we need to have a channel and queue object to start
     *
    */
    public function __construct($oChannel)
    {
        if(!$this->start($oChannel))
        {
            return $this->returnText(self::ERR_DEFAULT);
            die;
        }
    }

    public function getQueueUserByPosition($iPosition)
    {
        $iPosition = (int) $iPosition;
        $aQueueUsers = $this->channel->activeQueue->nextUsers($iPosition);
        if($aQueueUsers && isset($aQueueUsers[$iPosition-1]))
            return $aQueueUsers[$iPosition-1];

        return false;
    }

    /**
     * Returns current queue info
     *
    */
    public function info()
    {
        $iCount = $this->channel->activeQueue->queueUsers->count();
        $strRes = 'Current queue: "'. $this->channel->activeQueue->name .'", the queue is currently '. ($this->channel->activeQueue->is_open == 1 ? 'open for "'. $this->getUserLevel($this->channel->activeQueue->user_level) .'"' : 'closed') .' and contains '. $iCount .' user'. ($iCount == 1 ? '' : 's');

        if($this->channel->queues->count() > 0)
        {
            foreach($this->channel->queues as $oQueue)
            {
                $aQueueNames[] = $oQueue->name;
            }
            $strRes .= '. Available queues: ['. implode(', ', $aQueueNames) .']';
        }
        return $this->returnText($strRes);
    }

    /**
     * Returns url to full list of users in queue
     *
    */
    public function getList()
    {
        return $this->returnText('Full list of users and queues can be found here: https://nbq.gerhard.dev/'.  $this->channel->id);
    }

    /**
     * Clears the current queue
     *
     * @return string
    */
    public function clearQueue($iQueueId = false)
    {
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        $oQueue = $this->channel->queues->find($iQueueId === false ? $this->channel->active : $iQueueId);
        if($oQueue)
            $oQueue->queueUsers()->delete();

        return $this->returnText('Successfully cleared queue "'. $this->channel->activeQueue->name .'"');
    }

    /**
     * List next X persons from queue
     *
     * @return string
    */
    public function getListQueue($iLimit)
    {
        if($iLimit == 0)
            $iLimit = 5;
        elseif($iLimit > 50)
            $iLimit = 50;

        $aQueueUsers = $this->channel->activeQueue->nextUsers($iLimit);
        if($aQueueUsers->count() == 0)
            return $this->returnText('Unable to list queue "'. $this->channel->activeQueue->name .'", queue is empty');
        else
        {
            $aUsers = [];
            foreach($aQueueUsers as $oQueueUser)
            {
                $aUsers[] = $oQueueUser->user->displayName;
            }
            return $this->returnText('Next '. (count($aUsers) > 1 ? count($aUsers) .' persons' : 'person') .' in the queue "'. $this->channel->activeQueue->name .'": '. implode(', ', $aUsers));
        }
    }

    /**
     * Get next X person from queue
     *
     * @return string
    */
    public function getNext($strMessage, $bRandom = false)
    {
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        $iChars = 0;
        $iCharLimit = 150;
        $iLimit = 1;
        $iUserLevel = 1;
        $aMessage = array_values(array_filter(explode(' ', $strMessage)));

        if(isset($aMessage[0]))
            $iLimit = $aMessage[0];

        if(isset($aMessage[1]))
        {
            if(substr($aMessage[1], -1) == 's')
                $aMessage[1] = substr($aMessage[1], 0, -1);

            $iUserLevel = $this->getUserLevel($aMessage[1], true);
            if(!$iUserLevel)
                return $this->returnText('Invalid UserLevel provided: "'. $aMessage[1] .'". Available UserLevels: moderator, regular, subscriber, vip, everyone');
        }

        $iLimit = (int) $iLimit;
        if($iLimit == 0)
            $iLimit = 1;
        elseif($iLimit > 10)
            $iLimit = 10;

        $aQueueUsers = $this->channel->activeQueue->nextUsers($iLimit, $iUserLevel, $bRandom);
        if($aQueueUsers->isEmpty())
            return $this->returnText('Unable to get next person from queue "'. $this->channel->activeQueue->name .'", queue is empty');
        else
        {
            $aUsers = [];
            foreach($aQueueUsers as $oQueueUser)
            {
                $strTempRes = $oQueueUser->user->displayName . (trim($oQueueUser->message) == '' ? '' : ', with message: "'. $oQueueUser->message .'"');
                if(($iChars + strlen($strTempRes)) <= $iCharLimit)
                {
                    $aUsers[] = $strTempRes;
                    $iChars = $iChars + strlen($strTempRes);
                    $oQueueUser->forceDelete();
                }
            }
            return $this->returnText('Next '. (count($aUsers) > 1 ? count($aUsers) .' persons' : 'person') .' in the queue "'. $this->channel->activeQueue->name .'": '. implode(", ", $aUsers));
        }
    }

    /**
     * Opens the current queue for users to join
     *
     * @return string
    */
    public function openQueue()
    {
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        if($this->channel->activeQueue->is_open == 0)
        {
            $this->channel->activeQueue->is_open = 1;
            $this->channel->activeQueue->save();
            return $this->returnText('Queue "'. $this->channel->activeQueue->name .'" is now open');
        }
        else return $this->returnText('Queue "'. $this->channel->activeQueue->name .'" is already open');
    }

    /**
     * Closes the current queue
     *
     * @return string
    */
    public function closeQueue()
    {
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        if($this->channel->activeQueue->is_open == 1)
        {
            $this->channel->activeQueue->is_open = 0;
            $this->channel->activeQueue->save();
            return $this->returnText('Queue "'. $this->channel->activeQueue->name .'" is now closed');
        }
        else return $this->returnText('Queue "'. $this->channel->activeQueue->name .'" is already closed');
    }

    /**
     * Get the position of the current user in the current queue
     *
     * @return string
    */
    public function getPosition($oQueueUser = null, $bFull = true)
    {
        if(!$oQueueUser)
        {
            if(!$this->user)
                throw new Exception(self::ERR_NO_USER);

            $oQueueUser = $this->channel->activeQueue->getUser($this->user->id);
            if(!$oQueueUser)
                return $this->returnText('Cannot get position, you are not in queue "'. $this->channel->activeQueue->name .'"');
        }

        $iPosition = QueueUser::where([
            ['queue_id', '=', $this->channel->activeQueue->id],
            ['created_at', '<', $oQueueUser->created_at]
        ])->count();

        $iPosition++;
        return $bFull === true ? $this->returnText('Your current position in queue "'. $this->channel->activeQueue->name .'" is: #'. $iPosition) : $iPosition;
    }

    /**
     * Adds the current user to the current active queue
     *
     * @return string
    */
    public function joinQueue($strMessage)
    {
        if(!$this->channel->activeQueue->is_open)
            return $this->returnText('The queue "'. $this->channel->activeQueue->name .'" is currently closed');

        if(!$this->user)
            throw new Exception(self::ERR_NO_USER);

        if(!$this->isAllowed($this->getUserLevel($this->channel->activeQueue->user_level)))
            return $this->returnText('The queue "'. $this->channel->activeQueue->name .'" is currently only open for "'. $this->getUserLevel($this->channel->activeQueue->user_level) .'s"');

        if(strlen($strMessage) > 100)
            return $this->returnText('Error: Max length of user message is 100');

        $oQueueUser = $this->channel->activeQueue->getUser($this->user->id);
        if($oQueueUser)
        {
            if($oQueueUser->message != $strMessage)
            {
                $oQueueUser->message = $strMessage;
                $oQueueUser->save();
                return $this->returnText('You are already in queue "'. $this->channel->activeQueue->name .'" (position #'. $this->getPosition($oQueueUser, false) .'), your queue message has been updated');
            }
            else
                return $this->returnText('You are already in queue "'. $this->channel->activeQueue->name .'" (position #'. $this->getPosition($oQueueUser, false) .')');
        }
        else
        {
            if($this->channel->activeQueue->max_users != 0)
            {
                if($this->channel->activeQueue->queueUsers->count() >= $this->channel->activeQueue->max_users)
                    return $this->returnText('The queue "'. $this->channel->activeQueue->name .'" is currently full, limit: '. $this->channel->activeQueue->max_users);
            }

            $oQueueUser = QueueUser::create([
                'user_id' => $this->user->id,
                'queue_id' => $this->channel->active,
                'message' => $strMessage,
                'user_level' => $this->getUserLevel($this->user->userLevel, true)
            ]);

            if($oQueueUser)
                return $this->returnText('Successfully joined queue "'. $this->channel->activeQueue->name . '", your position is #'. $this->getPosition($oQueueUser, false));
        }
    }

    /**
     * Removes the current user to the current active queue
     *
     * @return string
    */
    public function leaveQueue()
    {
        if(!$this->user)
            return $this->returnText(self::ERR_NO_USER);

        $oQueueUser = $this->channel->activeQueue->getUser($this->user->id);
        if($oQueueUser)
        {
            $oQueueUser->forceDelete();
            return $this->returnText('Successfully removed from queue "'. $this->channel->activeQueue->name .'"');
        }
        else
            return $this->returnText('Unable to leave, you are not in queue "'. $this->channel->activeQueue->name .'"');
    }

    /**
     * Promotes a user by position to the first position of the current queue
     *
     * @return string
    */
    public function promoteUser($iPosition)
    {
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        if($iPosition == 0)
            return $this->returnText('Unable to promote user in queue, no position specified');

        $oQueueUser = $this->getQueueUserByPosition($iPosition);
        if($oQueueUser)
        {
            $aNextQueueUsers = $this->channel->activeQueue->nextUsers(1);
            if($aNextQueueUsers->count() == 1 && $aNextQueueUsers[0]->id != $oQueueUser->id)
            {
                $oQueueUser->created_at = strtotime($aNextQueueUsers[0]->created_at)-1;
                $oQueueUser->save();
                return $this->returnText('Successfully promoted '. $oQueueUser->user->displayName .' to first position of the queue');
            }
            else
                return $this->returnText('Unable to promote user in queue, user already first position in queue');
        }
        else
            return $this->returnText('Unable to promote user in queue, user not found');
    }

    /**
     * Sets the current channel and queue, returns true if succesfull
     * If a channel is not found yet, we add a default queue for them
     *
     * @return boolean
    */
    private function start($oNbChannel)
    {
        $oChannelOwner = $this->getUserInfo($oNbChannel);
        if($oChannelOwner)
        {
            if($oChannelOwner->channel)
            {
                $this->channel = $oChannelOwner->channel;
            }
            else
            {
                $oChannelByProviderId = Channel::where([
                    ['provider', '=', $oChannelOwner->provider],
                    ['provider_id', '=', $oChannelOwner->provider_id]
                ])->first();

                if($oChannelByProviderId)
                {
                    // We used to save by provider & providerId, updating this to userId now.
                    $oChannelByProviderId->user_id = $oChannelOwner->id;
                    $oChannelByProviderId->save();
                    $this->channel = $oChannelByProviderId;
                }
                else
                {
                    // New channel, provider(id) fields shouldn't be necessary anymore, we'll dump those once old records have been updated.
                    $oChannel = Channel::create([
                        'provider' => '',
                        'provider_id' => '',
                        'user_id' => $oChannelOwner->id
                    ]);
    
                    if($oChannel)
                    {
                        $this->channel = $oChannel;
                        $oQueue = $this->addQueue('default', 1);
                        if($oQueue)
                        {
                            $oChannel->active = $oQueue->id;
                            $oChannel->save();
                        }
                    }
                }

            }
        }

        if($this->channel && $this->channel->activeQueue)
            return true;

        return false;
    }

    public function getUserInfo($oNbUser)
    {
        $oUser = User::where([
            ['provider_id', '=', $oNbUser->providerId],
            ['provider', '=', $oNbUser->provider]
        ])->first();

        if(!$oUser)
        {
            $oUser = User::create([
                'provider' => $oNbUser->provider,
                'provider_id' => $oNbUser->providerId,
                'name' => $oNbUser->name,
                'displayName' => $oNbUser->displayName,
            ]);
        }
        else
        {
            if($oUser->name != $oNbUser->name || $oUser->displayName != $oNbUser->displayName)
            {
                $oUser->name = $oNbUser->name;
                $oUser->displayName = $oNbUser->displayName;
                $oUser->save();
            }
        }
        return $oUser;
    }

    /**
     * Sets the current user
     * If user doesn't exist yet, we add them to our database
     *
    */
    public function setUser($oNbUser)
    {
        $oUser = $this->getUserInfo($oNbUser);
        $oUser->userLevel = $oNbUser->userLevel;
        $this->user = $oUser;
    }

    /**
     * Adds a queue for a channel, return the new queue on success
     *
     * @return object | boolean
    */
    public function addQueue($strName, $iOpen = 0, $bText = false)
    {
        if($bText && !$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        if(trim($strName) == '')
            return $this->returnText('No queue name given');

        $oQueue = $this->channel->getQueue($strName);
        if(!$oQueue)
        {
            $oQueue = Queue::create([
                'channel_id' => $this->channel->id,
                'name' => $strName,
                'is_open' => ($iOpen == 0 ? 0 : 1)
            ]);
        }
        elseif($bText)
            return $this->returnText('Queue "'. $strName .'" already exists');

        if($bText === false)
        {
            if($oQueue)
                return $oQueue;

            return false;
        }
        elseif($oQueue)
            return $this->returnText('Successfully added queue "'. $strName .'"');
    }

    /**
     * Deletes a queue for a channel, clears the queue before deleting
     *
     * @return string
    */
    public function deleteQueue($strName)
    {
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        if(trim($strName) == '')
            return $this->returnText('No queue name given');

        if(strtolower(trim($strName)) == 'default')
            return $this->returnText('Cannot delete the "default" queue');

        $oQueue = $this->channel->getQueue($strName);
        if($oQueue)
        {
            if($this->channel->active == $oQueue->id)
            {
                // were deleting the current active queue so we have to set the default queue active again
                $oDefaultQueue = $this->channel->getQueue('default');
                if($oDefaultQueue)
                {
                    $this->channel->active = $oDefaultQueue->id;
                    $this->channel->save();
                }
            }

            $this->clearQueue($oQueue->id);
            $oQueue->forceDelete();
            return $this->returnText('Successfully deleted queue "'. $strName .'"');
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
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        if(trim($strName) == '')
            return $this->returnText('No queue name specified to set active');

        $oQueue = $this->channel->getQueue($strName);
        if($oQueue)
        {
            $this->channel->active = $oQueue->id;
            $this->channel->save();
            return $this->returnText('Queue "'. $oQueue->name .'" is now the active queue');
        }
        else
        {
            return $this->returnText('Unable to set queue "'. $strName .'", queue doesn\'t exist'); 
        }
    }

    /**
     * Remove user from queue by position
     *
     * @return string
    */
    public function removeQueueUser($iPosition)
    {
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        if($iPosition == 0)
            return $this->returnText('Unable to remove from queue, no position specified');

        $oQueueUser = $this->getQueueUserByPosition($iPosition);
        if($oQueueUser)
        {
            $oQueueUser->forceDelete();
            return $this->returnText('Successfully removed user from queue');
        }
        else
            return $this->returnText('Unable to remove from queue, user not found');
    }

    /**
     * Set limit of the current queue
     *
     * @return string
    */
    public function setQueueLimit($strLimit)
    {
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        if(trim($strLimit) == '')
            return $this->returnText('No queue limit provided');

        $iLimit = abs($strLimit);
        if($iLimit > 9999)
            return $this->returnText('Invalid queue limit provided, number must be less than 9999');
        else
        {
            $this->channel->activeQueue->max_users = $iLimit;
            $this->channel->activeQueue->save();
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
        $strReturnMessage = '';
        $strMessage = str_replace('queue "default"', 'queue', $strMessage);
        if($this->user)
            $strReturnMessage .= '@'. $this->user->displayName .': ';

        return substr($strReturnMessage . $strMessage, 0, 200);
    }

    /**
     * Sets the userLevel of the currenct queue
     *
     * @return string
    */
    public function setUserLevel($strUserLevel)
    {
        if(!$this->isAllowed('moderator'))
            return $this->returnText(self::ERR_NO_MOD);

        $strUserLevel = trim(strtolower($strUserLevel));
        if($iUserLevel = $this->getUserLevel($strUserLevel, true))
        {
            if($this->channel->activeQueue->user_level != $iUserLevel)
            {
                $this->channel->activeQueue->user_level = $iUserLevel;
                $this->channel->activeQueue->save();
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
        if($this->user)
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
            return in_array($this->user->userLevel, $aAllowed);
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
        if($xUserLevel == 'twitch_vip')
            $xUserLevel = 'vip';

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