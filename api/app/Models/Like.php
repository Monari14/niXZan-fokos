<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['id_user', 'id_new'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function news()
    {
        return $this->belongsTo(News::class, 'id_new');
    }
}
