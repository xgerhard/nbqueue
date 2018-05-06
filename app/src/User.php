<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;

class User extends Model 
{
    public $isModerator;

    protected $fillable = [
        'provider',
        'provider_id',
        'name',
        'displayName'
    ];
}
?>