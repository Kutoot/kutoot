<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('guard_name')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ])
                    ->default('web')
                    ->required(),
                CheckboxList::make('permissions')
                    ->relationship('permissions', 'name')
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(3),
            ]);
    }
}
