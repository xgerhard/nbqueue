<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;

class User extends Model 
{
    protected $fillable = [
        'provider',
        'provider_id',
        'name',
        'displayName'
    ];
}
?>