<?php

namespace App\Filament\Resources\FeaturedBanners\Pages;

use App\Filament\Resources\FeaturedBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedBanners extends ListRecords
{
    protected static string $resource = FeaturedBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
