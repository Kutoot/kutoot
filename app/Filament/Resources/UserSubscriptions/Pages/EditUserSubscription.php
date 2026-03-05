<?php

namespace App\Filament\Resources\UserSubscriptions\Pages;

use App\Filament\Resources\UserSubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUserSubscription extends EditRecord
{
    protected static string $resource = UserSubscriptionResource::class;

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
            ->title('User Subscription Updated')
            ->body('The user subscription has been updated successfully.');
    }
}
