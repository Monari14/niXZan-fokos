<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'bio',
        'password',
        'avatar',
    ];

    protected $appends = ['avatar_url'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function settings()
    {
        return $this->hasOne(UserSettings::class);
    }
    public function noticias()
    {
        return $this->hasMany(News::class);
    }
    public function seguidores()
    {
        return $this->hasMany(Follower::class, 'id_seguindo');
    }

    public function seguindo()
    {
        return $this->hasMany(Follower::class, 'id_seguidor');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(storage_path('app/public/' . $this->avatar))) {
            return asset('s/' . $this->avatar);
        }
        return asset('i/avatar-default.png');
    }

}
