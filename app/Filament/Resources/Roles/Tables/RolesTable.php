<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
            //
        ])
            ->filters([
            //
        ])
            ->recordActions([
            EditAction::make(),
        ])
            ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
    }
}
