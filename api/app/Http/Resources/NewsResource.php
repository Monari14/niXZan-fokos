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
            'username'     => $this->user->username ?? null,
            'avatar'       => $this->user->avatar_url ?? null,
            'created_at'   => $this->created_at->toDateTimeString(),
            'created_at_human' => $this->created_at->diffForHumans(),
            'likes_count'  => $this->whenLoaded('likes', function() {
                return $this->likes->count();
            }, isset($this->likes_count) ? $this->likes_count : 0),
            'liked_by_me'  => auth()->check() ? $this->likes()->where('id_user', auth()->id())->exists() : false,
        ];
    }
}
