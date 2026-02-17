<?php

namespace App\Filament\Resources\MerchantLocations\Pages;

use App\Filament\Resources\MerchantLocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMerchantLocation extends EditRecord
{
    protected static string $resource = MerchantLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
