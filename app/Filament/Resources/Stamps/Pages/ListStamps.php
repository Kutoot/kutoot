<?php

namespace App\Filament\Resources\Stamps\Pages;

use App\Filament\Resources\StampResource;
use App\Models\Campaign;
use App\Models\User;
use App\Services\StampService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListStamps extends ListRecords
{
    protected static string $resource = StampResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gift_stamps')
                ->label('Gift Stamps')
                ->icon('heroicon-o-gift')
                ->color('success')
                ->form([
                    Select::make('user_id')
                        ->label('User')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => User::where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id'))
                        ->getOptionLabelUsing(fn ($value) => User::find($value)?->name)
                        ->required(),
                    Select::make('campaign_id')
                        ->label('Campaign')
                        ->searchable()
                        ->options(fn () => Campaign::where('is_active', true)->pluck('reward_name', 'id'))
                        ->required(),
                    TextInput::make('count')
                        ->label('Number of Stamps')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(100)
                        ->default(1),
                    TextInput::make('note')
                        ->label('Label / Note')
                        ->placeholder('e.g. Gift, Free, Promotional')
                        ->default('Gift')
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $user = User::findOrFail($data['user_id']);
                    $campaign = Campaign::findOrFail($data['campaign_id']);
                    $stampService = app(StampService::class);

                    $awarded = $stampService->awardGiftStamps(
                        $user,
                        $campaign,
                        (int) $data['count'],
                        $data['note'] ?: 'Gift',
                    );

                    Notification::make()
                        ->title("{$awarded} gift stamp(s) awarded to {$user->name}")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
