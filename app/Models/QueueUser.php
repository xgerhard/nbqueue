<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueUser extends Model 
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'queue_id',
        'user_id',
        'message',
        'user_level'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function queue()
    {
        return $this->hasOne('App\Models\Queue', 'id', 'queue_id');
    }
}
?>