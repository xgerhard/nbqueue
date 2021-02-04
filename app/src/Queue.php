<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model 
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'is_open',
        'channel_id',
        'user_level',
        'max_users'
    ];

    public function channel()
    {
        return $this->hasOne('App\src\Channel', 'id', 'channel_id');
    }

    public function queueUsers()
    {
        return $this->hasMany('App\src\QueueUser', 'queue_id', 'id');
    }

    public function nextUsers($iLimit, $iUserLevel = 1, $bRandom = false)
    {
        return $this->queueUsers()
            ->where('user_level', '>=', $iUserLevel)
            ->orderByRaw($bRandom === false ? 'created_at asc' : 'rand()')
            ->limit($iLimit)
            ->get();
    }

    public function getUser($iUserId)
    {
        return $this->queueUsers()
            ->where('user_id', '=', $iUserId)
            ->first();
    }
}
?>