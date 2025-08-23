<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'news_count'  => $this->news()->count(),
            'created_at'  => $this->created_at->toDateTimeString(),
        ];
    }
}
