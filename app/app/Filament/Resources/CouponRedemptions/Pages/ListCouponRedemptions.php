<?php

namespace App\Filament\Resources\CouponRedemptions\Pages;

use App\Filament\Resources\CouponRedemptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCouponRedemptions extends ListRecords
{
    protected static string $resource = CouponRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
