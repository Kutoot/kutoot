<?php

namespace App\Filament\Resources\CouponRedemptions\Pages;

use App\Filament\Resources\CouponRedemptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCouponRedemption extends EditRecord
{
    protected static string $resource = CouponRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
