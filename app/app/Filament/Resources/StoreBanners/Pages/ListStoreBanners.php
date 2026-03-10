<?php

namespace App\Filament\Resources\StoreBanners\Pages;

use App\Filament\Resources\StoreBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStoreBanners extends ListRecords
{
    protected static string $resource = StoreBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
