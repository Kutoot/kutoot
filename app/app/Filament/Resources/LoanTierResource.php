<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanTiers\Pages\CreateLoanTier;
use App\Filament\Resources\LoanTiers\Pages\EditLoanTier;
use App\Filament\Resources\LoanTiers\Pages\ListLoanTiers;
use App\Filament\Resources\LoanTiers\Schemas\LoanTierForm;
use App\Filament\Resources\LoanTiers\Tables\LoanTiersTable;
use App\Models\LoanTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoanTierResource extends Resource
{
    protected static ?string $model = LoanTier::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Merchant Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return LoanTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoanTiersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoanTiers::route('/'),
            'create' => CreateLoanTier::route('/create'),
            'edit' => EditLoanTier::route('/{record}/edit'),
        ];
    }
}
