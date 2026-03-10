<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\NewsArticle
 */
class NewsArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'link_url' => $this->link_url,
            'published_at' => $this->published_at?->toISOString(),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'image_url' => $this->getFirstMediaUrl('image'),
            'thumb_url' => $this->getFirstMediaUrl('image', 'thumb'),
            'preview_url' => $this->getFirstMediaUrl('image', 'preview'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
