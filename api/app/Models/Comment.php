<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'id_new',
        'id_user',
        'content'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function noticia()
    {
        return $this->belongsTo(News::class, 'id_new');
    }
}
