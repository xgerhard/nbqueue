<?php
namespace App\OAuth;

use Illuminate\Database\Eloquent\Model;

class OAuthSession extends Model
{
    protected $fillable  = ['access_token', 'refresh_token', 'expires_in', 'provider', 'provider_id'];
}