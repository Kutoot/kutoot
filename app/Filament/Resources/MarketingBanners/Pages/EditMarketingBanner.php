<?php

namespace App\Filament\Resources\MarketingBanners\Pages;

use App\Filament\Resources\MarketingBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketingBanner extends EditRecord
{
    protected static string $resource = MarketingBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
