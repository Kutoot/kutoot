<?php

namespace App\Filament\Resources\CouponCategories\Pages;

use App\Filament\Resources\CouponCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCouponCategory extends EditRecord
{
    protected static string $resource = CouponCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
