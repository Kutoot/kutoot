<?php

namespace App\Filament\Resources\Stamps\Schemas;

use App\Enums\StampSource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StampForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('campaign_id')
                    ->relationship('campaign', 'reward_name')
                    ->required(),
                Select::make('transaction_id')
                    ->relationship('transaction', 'amount'),
                TextInput::make('code')
                    ->required(),
                Select::make('source')
                    ->options(StampSource::class)
                    ->default(StampSource::BillPayment)
                    ->required(),
            ]);
    }
}
