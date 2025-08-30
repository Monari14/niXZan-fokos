<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_id',
    ];

    // Relacionamento: uma notícia pertence a um usuário (autor)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
