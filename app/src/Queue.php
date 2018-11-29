<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model 
{
    public $displayName;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'is_open',
        'channel_id',
        'user_level'
    ];    
}
?>