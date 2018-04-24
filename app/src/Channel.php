<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model 
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider',
        'provider_id',
        'active',
    ];    
}
?>