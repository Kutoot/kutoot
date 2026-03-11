<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponCategories\Pages\CreateCouponCategory;
use App\Filament\Resources\CouponCategories\Pages\EditCouponCategory;
use App\Filament\Resources\CouponCategories\Pages\ListCouponCategories;
use App\Filament\Resources\CouponCategories\Schemas\CouponCategoryForm;
use App\Filament\Resources\CouponCategories\Tables\CouponCategoriesTable;
use App\Models\CouponCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CouponCategoryResource extends Resource
{
    protected static ?string $model = CouponCategory::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Coupons & Discounts';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CouponCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCouponCategories::route('/'),
            'create' => CreateCouponCategory::route('/create'),
            'edit' => EditCouponCategory::route('/{record}/edit'),
        ];
    }
}
