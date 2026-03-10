<?php

namespace App\Filament\Resources\CouponCategories\Pages;

use App\Filament\Resources\CouponCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCouponCategories extends ListRecords
{
    protected static string $resource = CouponCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
