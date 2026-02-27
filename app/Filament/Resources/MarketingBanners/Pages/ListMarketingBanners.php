<?php

namespace App\Filament\Resources\MarketingBanners\Pages;

use App\Filament\Resources\MarketingBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketingBanners extends ListRecords
{
    protected static string $resource = MarketingBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
