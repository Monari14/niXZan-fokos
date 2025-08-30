<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    protected $fillable = [
        'id_seguidor',
        'id_seguindo'
    ];

    public function seguidor()
    {
        return $this->belongsTo(User::class, 'id_seguidor');
    }

    public function seguindo()
    {
        return $this->belongsTo(User::class, 'id_seguindo');
    }
}
