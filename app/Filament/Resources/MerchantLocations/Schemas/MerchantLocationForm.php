<?php

namespace App\Filament\Resources\MerchantLocations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MerchantLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('merchant_id')
                    ->relationship('merchant', 'name')
                    ->required(),
                TextInput::make('branch_name')
                    ->required(),
                TextInput::make('commission_percentage')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
