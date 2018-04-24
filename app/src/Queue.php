<?php
namespace App\src;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model 
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];    
}
?>