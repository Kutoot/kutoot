<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponRedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'couponRedemptions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('coupon.title')
            ->columns([
                TextColumn::make('coupon.title')
                    ->label('Coupon')
                    ->searchable(),
                TextColumn::make('discount_applied')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('transaction.amount')
                    ->label('Transaction')
                    ->money('INR'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
