<?php

namespace App\Models;

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
        return $this->hasOne('App\Models\Channel', 'user_id', 'id');
    }
}
?>