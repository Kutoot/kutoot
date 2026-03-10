<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingBanners\Pages\CreateMarketingBanner;
use App\Filament\Resources\MarketingBanners\Pages\EditMarketingBanner;
use App\Filament\Resources\MarketingBanners\Pages\ListMarketingBanners;
use App\Filament\Resources\MarketingBanners\Schemas\MarketingBannerForm;
use App\Filament\Resources\MarketingBanners\Tables\MarketingBannersTable;
use App\Models\MarketingBanner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MarketingBannerResource extends Resource
{
    protected static ?string $model = MarketingBanner::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return MarketingBannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingBannersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingBanners::route('/'),
            'create' => CreateMarketingBanner::route('/create'),
            'edit' => EditMarketingBanner::route('/{record}/edit'),
        ];
    }
}
