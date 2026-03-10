<?php

namespace App\Filament\Resources\FeaturedBanners\Pages;

use App\Filament\Resources\FeaturedBannerResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateFeaturedBanner extends CreateRecord
{
    protected static string $resource = FeaturedBannerResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Featured Banner Created')
            ->body('The featured banner has been created successfully.');
    }
}
