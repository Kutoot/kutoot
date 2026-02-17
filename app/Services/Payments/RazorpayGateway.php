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

    public function createOrder(Transaction $transaction, array $options = []): array
    {
        $amountInPaise = (int) round($transaction->total_amount * 100);

        $orderData = [
            'receipt' => 'rcpt_'.$transaction->id,
            'amount' => $amountInPaise,
            'currency' => config('app.currency', 'INR'),
            'payment_capture' => 1, // Auto-capture
        ];

        // Check for split payment (Route)
        $razorpayAccountId = $transaction->merchantLocation->merchant->razorpay_account_id;

        if ($razorpayAccountId) {
            $merchantTotalAmountInPaise = (int) round($transaction->amount * 100);

            $orderData['transfers'] = [
                [
                    'account' => $razorpayAccountId,
                    'amount' => $merchantTotalAmountInPaise,
                    'currency' => config('app.currency', 'INR'),
                    'notes' => [
                        'merchant_payout' => 'Redemption for coupon',
                    ],
                    'on_hold' => false,
                ],
            ];
        }

        try {
            $razorpayOrder = $this->api->order->create($orderData);

            return [
                'id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'key' => config('app.razorpay.key_id'),
                'merchant_name' => $transaction->merchantLocation->merchant->name,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay Order Creation Failed: '.$e->getMessage());
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

    public function getName(): string
    {
        return 'razorpay';
    }
}
