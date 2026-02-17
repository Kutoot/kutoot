<?php

namespace App\Filament\Resources\QrCodes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QrCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            TextInput::make('unique_code')
            ->required(),
            TextInput::make('token')
            ->required(),
            Select::make('merchant_location_id')
            ->relationship('merchantLocation', 'id'),
            TextInput::make('status')
            ->required()
            ->default('available'),
            DateTimePicker::make('linked_at'),
            Select::make('linked_by')
            ->relationship('executive', 'name'),
        ]);
    }
}
