<?php

namespace App\Filament\Resources\CampaignCategories\Pages;

use App\Filament\Resources\CampaignCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCampaignCategory extends EditRecord
{
    protected static string $resource = CampaignCategoryResource::class;

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
            ->title('Campaign Category Updated')
            ->body('The campaign category has been updated successfully.');
    }
}
