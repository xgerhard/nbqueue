<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;
use App\src\User;

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
        return User::where([['provider_id', '=', $this->provider_id], ['provider', '=', $this->provider]])->first();
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