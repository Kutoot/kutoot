<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Permission Created')
            ->body('The permission has been created successfully.');
    }
}
