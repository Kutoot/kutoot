<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Filament\Resources\TransactionResource;
use App\Services\Payments\RazorpayGateway;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refund')
                ->label('Issue Refund')
                ->color('danger')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn () => $this->record->payment_status === PaymentStatus::Paid
                    || $this->record->payment_status === PaymentStatus::Completed)
                ->requiresConfirmation()
                ->modalHeading('Issue Refund')
                ->modalDescription('This will refund the payment via Razorpay and reverse any transfers.')
                ->form([
                    TextInput::make('refund_amount')
                        ->label('Refund Amount (₹)')
                        ->numeric()
                        ->default(fn () => (float) $this->record->total_amount)
                        ->required()
                        ->maxValue(fn () => (float) $this->record->total_amount)
                        ->minValue(0.01),
                    Textarea::make('reason')
                        ->label('Reason')
                        ->default('Requested by admin'),
                ])
                ->action(function (array $data): void {
                    $this->processRefund($data);
                }),
            DeleteAction::make(),
        ];
    }

    private function processRefund(array $data): void
    {
        $transaction = $this->record;
        $refundAmountInPaise = (int) round($data['refund_amount'] * 100);

        if (! $transaction->payment_id || $transaction->payment_id === '' || str_starts_with($transaction->payment_id, 'debug_')) {
            Notification::make()
                ->title('Cannot Refund')
                ->body('This transaction has no valid Razorpay payment ID.')
                ->danger()
                ->send();

            return;
        }

        try {
            $gateway = app(RazorpayGateway::class);

            // 1. Reverse transfer if this is a store order with a transfer
            if ($transaction->type === TransactionType::CouponRedemption && $transaction->transfer_id) {
                $storeShareInPaise = (int) round($transaction->amount * 100)
                    - (int) round($transaction->platform_fee * 100)
                    - (int) round($transaction->gst_amount * 100);

                if ($storeShareInPaise > 0) {
                    $reversal = $gateway->reverseTransfer($transaction->transfer_id, $storeShareInPaise);
                    Log::info('Transfer reversed', ['reversal' => $reversal, 'transaction_id' => $transaction->id]);
                }
            }

            // 2. Refund the payment
            $refund = $gateway->createRefund($transaction->payment_id, $refundAmountInPaise, [
                'notes' => [
                    'reason' => $data['reason'],
                    'admin_refund' => true,
                    'transaction_id' => $transaction->id,
                ],
            ]);

            // 3. Update transaction
            $transaction->update([
                'payment_status' => PaymentStatus::Refunded,
                'refund_id' => $refund['id'],
            ]);

            Notification::make()
                ->title('Refund Issued')
                ->body("Refund of ₹{$data['refund_amount']} processed. Refund ID: {$refund['id']}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Refund failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Refund Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
