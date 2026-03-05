<?php

namespace App\Filament\Resources\LoanTiers\Pages;

use App\Filament\Resources\LoanTierResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateLoanTier extends CreateRecord
{
    protected static string $resource = LoanTierResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Loan Tier Created')
            ->body('The loan tier has been created successfully.');
    }
}
