<?php

namespace App\Filament\Resources\FeaturedBanners\Pages;

use App\Filament\Resources\FeaturedBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
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

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Featured Banner Updated')
            ->body('The featured banner has been updated successfully.');
    }
}
