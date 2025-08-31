<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\NewsResource;

class PerfilResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'username'  => $this->username,
            'email'     => $this->email,
            'bio'       => $this->bio ?? '',
            'avatar'=> $this->avatar_url,
            'stats'     => [
                'seguindo'     => $this->seguindo()->count(),
                'seguidores'   => $this->seguidores()->count(),
                'fok_count'    => $this->noticias()->count(),
                'likes_count'  => $this->likes()->count(),
                'likes_received_count'   => $this->receivedLikes()->count(),
            ],
            'foks'      => NewsResource::collection($this->noticias()->latest()->get()),
        ];
    }
}
