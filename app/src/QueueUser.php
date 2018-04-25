<?php
namespace App\src;

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
        'message'
    ];

    public function user()
    {
        return $this->hasOne('App\src\User', 'id', 'user_id');
    }

    public function queue()
    {
        return $this->hasOne('App\src\Queue', 'id', 'queue_id');
    }
}
?>