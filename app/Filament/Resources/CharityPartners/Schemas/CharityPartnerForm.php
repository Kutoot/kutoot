<?php

namespace App\Filament\Resources\CharityPartners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CharityPartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->directory('sponsors')
                    ->maxSize(2048)
                    ->required(),
                TextInput::make('link')
                    ->label('Website URL')
                    ->url()
                    ->maxLength(2048),
                TextInput::make('serial')
                    ->label('Display Order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
