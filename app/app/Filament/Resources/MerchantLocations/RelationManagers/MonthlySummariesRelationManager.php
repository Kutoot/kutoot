<?php

namespace App\Filament\Resources\MerchantLocations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonthlySummariesRelationManager extends RelationManager
{
    protected static string $relationship = 'monthlySummaries';

    protected static ?string $title = 'Monthly Summaries';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('year')
                    ->sortable(),
                TextColumn::make('month')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => date('F', mktime(0, 0, 0, $state, 1))),
                TextColumn::make('total_bill_amount')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('total_commission_amount')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('net_amount')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('transaction_count')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('target_met')
                    ->boolean()
                    ->sortable(),
            ]);
    }
}
