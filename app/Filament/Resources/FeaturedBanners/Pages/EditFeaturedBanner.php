<?php

namespace App\Filament\Resources\FeaturedBanners\Pages;

use App\Filament\Resources\FeaturedBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeaturedBanner extends EditRecord
{
    protected static string $resource = FeaturedBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
