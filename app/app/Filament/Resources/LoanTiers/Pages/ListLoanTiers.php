<?php

namespace App\Filament\Resources\LoanTiers\Pages;

use App\Filament\Resources\LoanTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoanTiers extends ListRecords
{
    protected static string $resource = LoanTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
