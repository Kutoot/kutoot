<?php

namespace App\Filament\Resources\MerchantLocations\RelationManagers;

use App\Enums\LoanStatus;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoansRelationManager extends RelationManager
{
    protected static string $relationship = 'loans';

    protected static ?string $title = 'Loans';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('loan_tier_id')
                    ->relationship('loanTier', 'description')
                    ->nullable(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('₹'),
                Select::make('status')
                    ->options(LoanStatus::class)
                    ->default(LoanStatus::Active)
                    ->required(),
                TextInput::make('streak_months_at_approval')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                DateTimePicker::make('approved_at')
                    ->default(now()),
                Textarea::make('notes')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('amount')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('loanTier.description')
                    ->label('Tier')
                    ->placeholder('—'),
                TextColumn::make('streak_months_at_approval')
                    ->label('Streak (months)')
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('streak_broken_at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
