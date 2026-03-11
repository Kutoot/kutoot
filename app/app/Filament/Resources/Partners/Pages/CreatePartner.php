<?php

namespace App\Filament\Resources\Partners\Pages;

use App\Filament\Resources\Partners\PartnerResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'Partner';

        return $data;
    }

    protected function afterCreate(): void
    {
        Cache::forget('sponsors:active');
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Partner Created')
            ->body('The partner has been created successfully.');
    }
}
