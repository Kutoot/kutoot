<?php

namespace App\Filament\Resources\SubscriptionPlans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('price')
                    ->money('INR')
                    ->sortable(),
                IconColumn::make('best_value')
                    ->boolean()
                    ->label('Best Value')
                    ->tooltip('Marked as best-value / recommended plan'),
                TextColumn::make('original_price')
                    ->label('MRP')
                    ->money('INR')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default'),
                TextColumn::make('stamps_on_purchase')
                    ->label('Bonus Stamps')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stamps_per_denomination')
                    ->label('Transaction Stamps')
                    ->formatStateUsing(function ($state, $record): string {
                        $stamps = $record->stamps_per_denomination ?? 0;
                        $denom = $record->stamp_denomination ?? 0;

                        if ($stamps <= 0 || $denom <= 0) {
                            return '—';
                        }

                        return "{$stamps} stamps per ₹{$denom} bill";
                    })
                    ->sortable(),
                TextColumn::make('max_discounted_bills')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_redeemable_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('duration_days')
                    ->label('Duration')
                    ->suffix(' days')
                    ->placeholder('∞')
                    ->sortable(),
                TextColumn::make('campaigns_count')
                    ->counts('campaigns')
                    ->label('Campaigns')
                    ->sortable(),
                TextColumn::make('coupon_categories_count')
                    ->counts('couponCategories')
                    ->label('Coupon Access')
                    ->formatStateUsing(function ($state, $record): string {
                        $categories = $record->couponCategories()->pluck('name');

                        if ($categories->isEmpty()) {
                            return 'No deals';
                        }

                        return 'Access to: '.$categories->join(' + ').' deals';
                    })
                    ->sortable()
                    ->wrap(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
