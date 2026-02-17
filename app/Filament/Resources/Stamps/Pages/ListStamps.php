<?php

namespace App\Filament\Resources\Stamps\Pages;

use App\Filament\Resources\StampResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStamps extends ListRecords
{
    protected static string $resource = StampResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
