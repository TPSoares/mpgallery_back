<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photos extends Model
{
    protected $fillable = [
        'title', 'description', 'user_id', 'path'
    ];
}
