<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    protected $fillable = [
        'id_user',
        'theme',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
