<?php

namespace App\Filament\Resources\MarketingBanners\Pages;

use App\Filament\Resources\MarketingBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
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

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Marketing Banner Updated')
            ->body('The marketing banner has been updated successfully.');
    }
}
