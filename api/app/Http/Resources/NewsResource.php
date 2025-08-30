<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'content'      => $this->content,
            'author'       => $this->user->name ?? null,
            'username'    => $this->user->username ?? null,
            'created_at'   => $this->created_at->toDateTimeString(),
        ];
    }
}
