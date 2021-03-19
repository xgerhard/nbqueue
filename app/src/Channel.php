<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model 
{
    protected $fillable = [
        'provider',
        'provider_id',
        'active',
        'user_id',
        'lang',
        'token'
    ];

    public function channelOwner()
    {
        return $this->hasOne('App\src\User', 'id', 'user_id');
    }

    public function queues()
    {
        return $this->hasMany('App\src\Queue', 'channel_id', 'id');
    }

    public function activeQueue()
    {
        return $this->hasOne('App\src\Queue', 'id', 'active');
    }

    public function getQueue($strName)
    {
        return $this->queues()
            ->where('name', '=', $strName)
            ->first();
    }
}
?>