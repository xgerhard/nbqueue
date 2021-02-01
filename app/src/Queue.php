<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model 
{
    public $displayName;

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
}
?>