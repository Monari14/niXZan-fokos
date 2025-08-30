<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['user_id', 'momento_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function new()
    {
        return $this->belongsTo(News::class);
    }
}
