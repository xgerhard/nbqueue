<?php

namespace App\OAuth;

use Illuminate\Database\Eloquent\Model;

class OAuthProvider extends Model
{
    protected $fillable = ['name'];

    public function sessions()
    {
        return $this->hasMany('App\OAuth\OAuthSession', 'provider_id', 'id');
    }

    public function lastSession()
    {
        return $this->sessions()
            ->orderBy('created_at', 'desc')
            ->first();
    }
}