<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model 
{
    public $name;
    public $displayName;

    protected $fillable = [
        'provider',
        'provider_id',
        'active',
    ];
}
?>