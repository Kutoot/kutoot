<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\MerchantCategory
 */
class MerchantCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image ? url(\Illuminate\Support\Facades\Storage::url($this->image)) : null,
            'icon' => $this->icon ? url(\Illuminate\Support\Facades\Storage::url($this->icon)) : null,
            'serial' => $this->serial,
        ];
    }
}
