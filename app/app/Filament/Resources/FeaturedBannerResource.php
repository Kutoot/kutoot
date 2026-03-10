<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedBanners\Pages\CreateFeaturedBanner;
use App\Filament\Resources\FeaturedBanners\Pages\EditFeaturedBanner;
use App\Filament\Resources\FeaturedBanners\Pages\ListFeaturedBanners;
use App\Filament\Resources\FeaturedBanners\Schemas\FeaturedBannerForm;
use App\Filament\Resources\FeaturedBanners\Tables\FeaturedBannersTable;
use App\Models\FeaturedBanner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeaturedBannerResource extends Resource
{
    protected static ?string $model = FeaturedBanner::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return FeaturedBannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeaturedBannersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeaturedBanners::route('/'),
            'create' => CreateFeaturedBanner::route('/create'),
            'edit' => EditFeaturedBanner::route('/{record}/edit'),
        ];
    }
}
