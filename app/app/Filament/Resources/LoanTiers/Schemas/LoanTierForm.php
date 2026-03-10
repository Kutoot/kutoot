<?php

namespace App\Filament\Resources\LoanTiers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LoanTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('min_streak_months')
                    ->label('Minimum Streak (Months)')
                    ->helperText('Location must have this many consecutive months meeting their target to qualify for this tier.')
                    ->required()
                    ->numeric()
                    ->minValue(3),
                TextInput::make('max_loan_amount')
                    ->label('Maximum Loan Amount (₹)')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->prefix('₹'),
                TextInput::make('interest_rate_percentage')
                    ->label('Interest Rate (%)')
                    ->numeric()
                    ->nullable()
                    ->suffix('%'),
                TextInput::make('description')
                    ->label('Description')
                    ->helperText('A short label for this tier, e.g. "3-month starter loan".')
                    ->nullable(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
