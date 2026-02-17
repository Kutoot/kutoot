<?php

namespace App\Filament\Resources\Stamps\Pages;

use App\Filament\Resources\StampResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStamp extends EditRecord
{
    protected static string $resource = StampResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
