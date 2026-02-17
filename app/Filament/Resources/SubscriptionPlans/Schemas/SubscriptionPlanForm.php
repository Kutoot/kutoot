<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Toggle::make('is_default')
                    ->label('Default Plan')
                    ->helperText('New users will be assigned to this plan automatically.'),

                Section::make('Stamp Configuration')
                    ->schema([
                        TextInput::make('stamps_on_purchase')
                            ->label('Bonus Stamps on Purchase')
                            ->helperText('Number of stamps awarded when a user purchases this plan.')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('stamps_per_100')
                            ->label('Stamps per ₹100 Bill')
                            ->helperText('Number of stamps awarded for every ₹100 spent on a bill.')
                            ->required()
                            ->numeric()
                            ->default(1),
                    ]),

                Section::make('Billing Limits')
                    ->schema([
                        TextInput::make('max_discounted_bills')
                            ->required()
                            ->numeric(),
                        TextInput::make('max_redeemable_amount')
                            ->required()
                            ->numeric(),
                    ]),

                Section::make('Access')
                    ->schema([
                        CheckboxList::make('campaigns')
                            ->relationship('campaigns', 'reward_name')
                            ->label('Eligible Campaigns')
                            ->helperText('Users on this plan can subscribe to these campaigns.')
                            ->bulkToggleable()
                            ->columns(2),
                        CheckboxList::make('couponCategories')
                            ->relationship('couponCategories', 'name')
                            ->label('Eligible Coupon Categories')
                            ->helperText('Users on this plan can use coupons from these categories.')
                            ->bulkToggleable()
                            ->columns(2),
                    ]),
            ]);
    }
}
