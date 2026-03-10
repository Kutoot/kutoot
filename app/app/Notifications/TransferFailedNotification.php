<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $transferId,
        public ?int $transactionId,
        public string $errorMessage,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Razorpay Transfer Failed — Immediate Action Required')
            ->error()
            ->greeting('Transfer Failure Alert')
            ->line('A Razorpay Route transfer to a linked account has failed.')
            ->line("**Transfer ID:** {$this->transferId}")
            ->line("**Error:** {$this->errorMessage}");

        if ($this->transactionId) {
            $message->line("**Transaction ID:** {$this->transactionId}");
        }

        return $message
            ->line('This may indicate the merchant\'s KYC is pending or their linked account has issues.')
            ->action('View in Admin Panel', url('/admin/transactions'))
            ->line('Please investigate and resolve this promptly.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'transfer_failed',
            'transfer_id' => $this->transferId,
            'transaction_id' => $this->transactionId,
            'error_message' => $this->errorMessage,
        ];
    }
}
