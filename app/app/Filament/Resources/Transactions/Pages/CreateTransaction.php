<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Transaction Created')
            ->body('The transaction has been created successfully.');
    }
}
