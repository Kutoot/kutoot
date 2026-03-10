<?php

namespace App\Filament\Resources\DiscountCoupons\Tables;

use App\Enums\ApprovalStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Collection;

class DiscountCouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('merchantLocation.branch_name')
                    ->label('Merchant Location')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('discount_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('discount_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_order_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('usage_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('usage_per_user')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('approval_status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('approval_status')
                    ->options(ApprovalStatus::class)
                    ->label('Approval Status'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->approval_status !== ApprovalStatus::Approved)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'approval_status' => ApprovalStatus::Approved,
                            'is_active' => true,
                            'rejection_reason' => null,
                        ]);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->approval_status !== ApprovalStatus::Rejected)
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Reason for rejection')
                            ->placeholder('Optional: explain why this deal was rejected')
                            ->maxLength(500),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'approval_status' => ApprovalStatus::Rejected,
                            'is_active' => false,
                            'rejection_reason' => $data['rejection_reason'] ?? null,
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn ($record) => $record->update([
                            'approval_status' => ApprovalStatus::Approved,
                            'is_active' => true,
                            'rejection_reason' => null,
                        ]))),
                    BulkAction::make('bulk_reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn ($record) => $record->update([
                            'approval_status' => ApprovalStatus::Rejected,
                            'is_active' => false,
                        ]))),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
