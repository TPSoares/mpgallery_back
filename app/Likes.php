<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Likes extends Model
{
    //
    protected $fillable = [
        'user_id', 'photo_id'
    ];
}
