<?php

namespace App\Filament\Resources\CampaignCategories\Pages;

use App\Filament\Resources\CampaignCategoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCampaignCategory extends CreateRecord
{
    protected static string $resource = CampaignCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Campaign Category Created')
            ->body('The campaign category has been created successfully.');
    }
}
