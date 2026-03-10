<?php

namespace App\Filament\Resources\NewsArticles\Pages;

use App\Filament\Resources\NewsArticleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsArticle extends CreateRecord
{
    protected static string $resource = NewsArticleResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('News Article Created')
            ->body('The news article has been created successfully.');
    }
}
