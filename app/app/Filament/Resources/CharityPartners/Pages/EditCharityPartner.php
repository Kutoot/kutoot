<?php

namespace App\Filament\Resources\CharityPartners\Pages;

use App\Filament\Resources\CharityPartners\CharityPartnerResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditCharityPartner extends EditRecord
{
    protected static string $resource = CharityPartnerResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = 'Charity Partner';

        return $data;
    }

    protected function afterSave(): void
    {
        Cache::forget('sponsors:active');
    }

    protected function afterDelete(): void
    {
        Cache::forget('sponsors:active');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(fn () => Cache::forget('sponsors:active')),
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
            ->title('Charity Partner Updated')
            ->body('The charity partner has been updated successfully.');
    }
}
