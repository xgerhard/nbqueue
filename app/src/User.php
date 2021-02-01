<?php
namespace App\src;

use App\src\Channel;
use Illuminate\Database\Eloquent\Model;

class User extends Model 
{
    public $userLevel;

    protected $fillable = [
        'provider',
        'provider_id',
        'name',
        'displayName'
    ];

    public function channel()
    {
        return Channel::where([['provider_id', '=', $this->provider_id], ['provider', '=', $this->provider]])->first();
        return $this->hasOne('App\src\Channel', 'user_id', 'id');
    }
}
?>