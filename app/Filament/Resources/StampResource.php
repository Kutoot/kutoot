<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Stamps\Pages\CreateStamp;
use App\Filament\Resources\Stamps\Pages\EditStamp;
use App\Filament\Resources\Stamps\Pages\ListStamps;
use App\Filament\Resources\Stamps\Schemas\StampForm;
use App\Filament\Resources\Stamps\Tables\StampsTable;
use App\Models\Stamp;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StampResource extends Resource
{
    protected static ?string $model = Stamp::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|\UnitEnum|null $navigationGroup = 'Campaign & Rewards';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return StampForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StampsTable::configure($table);
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
            'index' => ListStamps::route('/'),
            'create' => CreateStamp::route('/create'),
            'edit' => EditStamp::route('/{record}/edit'),
        ];
    }
}
