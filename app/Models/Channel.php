<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model 
{
    protected $fillable = [
        'provider',
        'provider_id',
        'active',
        'user_id',
        'lang'
    ];

    public function channelOwner()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function queues()
    {
        return $this->hasMany('App\Models\Queue', 'channel_id', 'id');
    }

    public function activeQueue()
    {
        return $this->hasOne('App\Models\Queue', 'id', 'active');
    }

    public function getQueue($strName)
    {
        return $this->queues()
            ->where('name', '=', $strName)
            ->first();
    }
}
?>