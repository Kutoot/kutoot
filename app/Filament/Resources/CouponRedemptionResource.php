<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponRedemptions\Pages\CreateCouponRedemption;
use App\Filament\Resources\CouponRedemptions\Pages\EditCouponRedemption;
use App\Filament\Resources\CouponRedemptions\Pages\ListCouponRedemptions;
use App\Filament\Resources\CouponRedemptions\Schemas\CouponRedemptionForm;
use App\Filament\Resources\CouponRedemptions\Tables\CouponRedemptionsTable;
use App\Models\CouponRedemption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CouponRedemptionResource extends Resource
{
    protected static ?string $model = CouponRedemption::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|\UnitEnum|null $navigationGroup = 'Coupons & Discounts';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CouponRedemptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponRedemptionsTable::configure($table);
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
            'index' => ListCouponRedemptions::route('/'),
            'create' => CreateCouponRedemption::route('/create'),
            'edit' => EditCouponRedemption::route('/{record}/edit'),
        ];
    }
}
