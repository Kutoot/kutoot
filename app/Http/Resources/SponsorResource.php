<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Sponsor
 */
class SponsorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'logo' => $this->logo ? url(Storage::url($this->logo)) : null,
            'banner' => $this->banner ? url(Storage::url($this->banner)) : null,
            'link' => $this->link,
        ];
    }
}
