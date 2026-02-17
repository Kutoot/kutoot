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
     * Get the gateway name.
     */
    public function getName(): string;
}
