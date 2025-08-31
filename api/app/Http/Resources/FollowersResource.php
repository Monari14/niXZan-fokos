<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'username' => $this->username,
            'avatar'   => $this->avatar_url,
        ];
    }
}
