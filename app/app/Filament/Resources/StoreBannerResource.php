<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreBanners\Pages\CreateStoreBanner;
use App\Filament\Resources\StoreBanners\Pages\EditStoreBanner;
use App\Filament\Resources\StoreBanners\Pages\ListStoreBanners;
use App\Filament\Resources\StoreBanners\Schemas\StoreBannerForm;
use App\Filament\Resources\StoreBanners\Tables\StoreBannersTable;
use App\Models\StoreBanner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StoreBannerResource extends Resource
{
    protected static ?string $model = StoreBanner::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return StoreBannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoreBannersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStoreBanners::route('/'),
            'create' => CreateStoreBanner::route('/create'),
            'edit' => EditStoreBanner::route('/{record}/edit'),
        ];
    }
}
