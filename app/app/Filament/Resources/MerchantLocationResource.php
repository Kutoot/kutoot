<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MerchantLocations\Pages\CreateMerchantLocation;
use App\Filament\Resources\MerchantLocations\Pages\EditMerchantLocation;
use App\Filament\Resources\MerchantLocations\Pages\ListMerchantLocations;
use App\Filament\Resources\MerchantLocations\RelationManagers\LoansRelationManager;
use App\Filament\Resources\MerchantLocations\RelationManagers\MonthlySummariesRelationManager;
use App\Filament\Resources\MerchantLocations\RelationManagers\QrCodesRelationManager;
use App\Filament\Resources\MerchantLocations\Schemas\MerchantLocationForm;
use App\Filament\Resources\MerchantLocations\Tables\MerchantLocationsTable;
use App\Models\MerchantLocation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MerchantLocationResource extends Resource
{
    protected static ?string $model = MerchantLocation::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Merchant Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return MerchantLocationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantLocationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            QrCodesRelationManager::class,
            MonthlySummariesRelationManager::class,
            LoansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchantLocations::route('/'),
            'create' => CreateMerchantLocation::route('/create'),
            'edit' => EditMerchantLocation::route('/{record}/edit'),
        ];
    }
}
