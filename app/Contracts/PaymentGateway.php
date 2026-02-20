<?php

namespace App\Contracts;

use App\Models\Transaction;

interface PaymentGateway
{
    /**
     * Create a new payment order.
     */
    public function createOrder(Transaction $transaction, array $options = []): array;

    /**
     * Verify the payment from the gateway.
     */
    public function verifyPayment(array $params): bool;

    /**
     * Create a refund for a payment.
     *
     * @return array{id: string, amount: int, status: string}
     */
    public function createRefund(string $paymentId, int $amountInPaise, array $options = []): array;

    /**
     * Fetch payment details from the gateway.
     *
     * @return array<string, mixed>
     */
    public function fetchPayment(string $paymentId): array;

    /**
     * Get the gateway name.
     */
    public function getName(): string;
}
