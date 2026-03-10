<?php

namespace App\Filament\Resources\MarketingBanners\Pages;

use App\Filament\Resources\MarketingBannerResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketingBanner extends CreateRecord
{
    protected static string $resource = MarketingBannerResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Marketing Banner Created')
            ->body('The marketing banner has been created successfully.');
    }
}
