<?php
namespace App\OAuth;

use Illuminate\Database\Eloquent\Model;

class OAuthProvider extends Model
{
    protected $fillable = ['name'];
}