<?php

namespace App\Filament\Resources\MerchantLocations\Pages;

use App\Filament\Resources\MerchantLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerchantLocations extends ListRecords
{
    protected static string $resource = MerchantLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
