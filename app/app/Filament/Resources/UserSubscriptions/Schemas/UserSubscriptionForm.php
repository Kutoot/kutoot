<?php

namespace App\Filament\Resources\UserSubscriptions\Schemas;

use App\Enums\SubscriptionStatus;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class UserSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->required(),
                Select::make('status')
                    ->options(SubscriptionStatus::class)
                    ->default('active')
                    ->required(),
            ]);
    }
}
