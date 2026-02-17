<?php

namespace App\Filament\Resources\DiscountCoupons\Pages;

use App\Filament\Resources\DiscountCouponResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscountCoupon extends EditRecord
{
    protected static string $resource = DiscountCouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
