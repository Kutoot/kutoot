<?php

namespace App\Filament\Resources\LoanTiers\Pages;

use App\Filament\Resources\LoanTierResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLoanTier extends EditRecord
{
    protected static string $resource = LoanTierResource::class;

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
            ->title('Loan Tier Updated')
            ->body('The loan tier has been updated successfully.');
    }
}
