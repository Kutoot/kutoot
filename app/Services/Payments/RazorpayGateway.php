<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class RazorpayGateway implements PaymentGateway
{
    protected Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('app.razorpay.key_id'),
            config('app.razorpay.key_secret')
        );
    }

    /**
     * Create a store/coupon redemption order with optional Route transfer.
     */
    public function createOrder(Transaction $transaction, array $options = []): array
    {
        $amountInPaise = (int) round($transaction->total_amount * 100);

        $orderData = [
            'receipt' => 'rcpt_'.$transaction->id,
            'amount' => $amountInPaise,
            'currency' => config('app.currency', 'INR'),
            'payment_capture' => 1,
        ];

        // Check for split payment (Route)
        $razorpayAccountId = $transaction->merchantLocation?->merchant?->razorpay_account_id;

        if ($razorpayAccountId) {
            // Store share = bill after discount minus platform fee + GST (Kutoot's cut)
            $storeShareInPaise = (int) round($transaction->amount * 100)
                - (int) round($transaction->platform_fee * 100)
                - (int) round($transaction->gst_amount * 100);

            if ($storeShareInPaise > 0) {
                $orderData['transfers'] = [
                    [
                        'account' => $razorpayAccountId,
                        'amount' => $storeShareInPaise,
                        'currency' => config('app.currency', 'INR'),
                        'notes' => [
                            'merchant_payout' => 'Redemption for coupon',
                            'transaction_id' => (string) $transaction->id,
                        ],
                        'on_hold' => false,
                    ],
                ];
            }
        }

        try {
            $headers = [];
            if ($transaction->idempotency_key) {
                $headers['X-Razorpay-Idempotency-Key'] = $transaction->idempotency_key;
            }

            $razorpayOrder = $this->api->order->create($orderData, $headers);

            return [
                'id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'key' => config('app.razorpay.key_id'),
                'merchant_name' => $transaction->merchantLocation?->merchant?->name ?? 'Kutoot',
                'transfers' => $razorpayOrder['transfers'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay Order Creation Failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
            ]);
            throw $e;
        }
    }

    /**
     * Create a plan purchase order (no Route transfer — all revenue to Kutoot).
     */
    public function createPlanOrder(Transaction $transaction): array
    {
        $amountInPaise = (int) round($transaction->total_amount * 100);

        $orderData = [
            'receipt' => 'plan_rcpt_'.$transaction->id,
            'amount' => $amountInPaise,
            'currency' => config('app.currency', 'INR'),
            'payment_capture' => 1,
            'notes' => [
                'type' => 'plan_purchase',
                'transaction_id' => (string) $transaction->id,
            ],
        ];

        try {
            $headers = [];
            if ($transaction->idempotency_key) {
                $headers['X-Razorpay-Idempotency-Key'] = $transaction->idempotency_key;
            }

            $razorpayOrder = $this->api->order->create($orderData, $headers);

            return [
                'id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'key' => config('app.razorpay.key_id'),
                'merchant_name' => 'Kutoot',
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay Plan Order Creation Failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
            ]);
            throw $e;
        }
    }

    public function verifyPayment(array $params): bool
    {
        try {
            $attributes = [
                'razorpay_order_id' => $params['razorpay_order_id'],
                'razorpay_payment_id' => $params['razorpay_payment_id'],
                'razorpay_signature' => $params['razorpay_signature'],
            ];

            $this->api->utility->verifyPaymentSignature($attributes);

            return true;
        } catch (\Exception $e) {
            Log::error('Razorpay Signature Verification Failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Create a refund for a payment.
     *
     * @return array{id: string, amount: int, status: string}
     */
    public function createRefund(string $paymentId, int $amountInPaise, array $options = []): array
    {
        try {
            $refundData = ['amount' => $amountInPaise];

            if (! empty($options['notes'])) {
                $refundData['notes'] = $options['notes'];
            }

            if (! empty($options['speed'])) {
                $refundData['speed'] = $options['speed'];
            }

            $refund = $this->api->payment->fetch($paymentId)->refund($refundData);

            return [
                'id' => $refund['id'],
                'amount' => $refund['amount'],
                'status' => $refund['status'],
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay Refund Failed: '.$e->getMessage(), [
                'payment_id' => $paymentId,
                'amount' => $amountInPaise,
            ]);
            throw $e;
        }
    }

    /**
     * Reverse a Route transfer.
     *
     * @return array{id: string, amount: int}
     */
    public function reverseTransfer(string $transferId, int $amountInPaise): array
    {
        try {
            $reversal = $this->api->transfer->fetch($transferId)->reverse(['amount' => $amountInPaise]);

            return [
                'id' => $reversal['id'],
                'amount' => $reversal['amount'],
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay Transfer Reversal Failed: '.$e->getMessage(), [
                'transfer_id' => $transferId,
                'amount' => $amountInPaise,
            ]);
            throw $e;
        }
    }

    /**
     * Fetch payment details from Razorpay.
     *
     * @return array<string, mixed>
     */
    public function fetchPayment(string $paymentId): array
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);

            return $payment->toArray();
        } catch (\Exception $e) {
            Log::error('Razorpay Payment Fetch Failed: '.$e->getMessage(), [
                'payment_id' => $paymentId,
            ]);
            throw $e;
        }
    }

    public function getName(): string
    {
        return 'razorpay';
    }
}
