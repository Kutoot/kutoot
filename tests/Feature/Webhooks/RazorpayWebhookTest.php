<?php

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\TransferFailedNotification;
use Illuminate\Support\Facades\Notification;

function validWebhookPayload(string $event, array $entityOverrides = []): array
{
    $payloads = [
        'payment.captured' => [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => array_merge([
                        'id' => 'pay_webhook123',
                        'order_id' => 'order_webhook123',
                        'amount' => 100000,
                        'currency' => 'INR',
                        'status' => 'captured',
                    ], $entityOverrides),
                ],
            ],
        ],
        'payment.failed' => [
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => array_merge([
                        'id' => 'pay_failed123',
                        'order_id' => 'order_failed123',
                        'status' => 'failed',
                    ], $entityOverrides),
                ],
            ],
        ],
        'refund.created' => [
            'event' => 'refund.created',
            'payload' => [
                'refund' => [
                    'entity' => array_merge([
                        'id' => 'rfnd_webhook123',
                        'payment_id' => 'pay_refund123',
                        'amount' => 100000,
                        'status' => 'processed',
                    ], $entityOverrides),
                ],
            ],
        ],
        'transfer.failed' => [
            'event' => 'transfer.failed',
            'payload' => [
                'transfer' => [
                    'entity' => array_merge([
                        'id' => 'trf_failed123',
                        'error' => ['description' => 'KYC pending'],
                    ], $entityOverrides),
                ],
            ],
        ],
    ];

    return $payloads[$event] ?? ['event' => $event];
}

function signPayload(string $payload, string $secret): string
{
    return hash_hmac('sha256', $payload, $secret);
}

beforeEach(function () {
    config(['app.razorpay.webhook_secret' => 'test_webhook_secret']);
});

test('webhook rejects invalid signature', function () {
    $payload = json_encode(validWebhookPayload('payment.captured'));

    $response = $this->postJson('/api/webhooks/razorpay', json_decode($payload, true), [
        'X-Razorpay-Signature' => 'invalid_signature',
        'Content-Type' => 'application/json',
    ]);

    $response->assertStatus(401);
});

test('webhook accepts valid signature', function () {
    $payload = json_encode(validWebhookPayload('payment.captured'));
    $signature = signPayload($payload, 'test_webhook_secret');

    // Create transaction for the order
    Transaction::factory()->create([
        'razorpay_order_id' => 'order_webhook123',
        'payment_status' => PaymentStatus::Pending,
        'type' => TransactionType::CouponRedemption,
    ]);

    $response = $this->call('POST', '/api/webhooks/razorpay', [], [], [], [
        'HTTP_X-Razorpay-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $response->assertSuccessful();
});

test('payment.captured webhook marks transaction as paid', function () {
    $transaction = Transaction::factory()->create([
        'razorpay_order_id' => 'order_cap123',
        'payment_status' => PaymentStatus::Pending,
        'type' => TransactionType::PlanPurchase,
    ]);

    $data = validWebhookPayload('payment.captured', [
        'id' => 'pay_cap123',
        'order_id' => 'order_cap123',
    ]);
    $payload = json_encode($data);
    $signature = signPayload($payload, 'test_webhook_secret');

    $this->call('POST', '/api/webhooks/razorpay', [], [], [], [
        'HTTP_X-Razorpay-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $transaction->refresh();
    expect($transaction->payment_status)->toBe(PaymentStatus::Paid)
        ->and($transaction->payment_id)->toBe('pay_cap123');
});

test('payment.captured webhook is idempotent for already paid transactions', function () {
    $transaction = Transaction::factory()->create([
        'razorpay_order_id' => 'order_idem123',
        'payment_status' => PaymentStatus::Paid,
        'payment_id' => 'pay_existing',
        'type' => TransactionType::CouponRedemption,
    ]);

    $data = validWebhookPayload('payment.captured', [
        'id' => 'pay_new',
        'order_id' => 'order_idem123',
    ]);
    $payload = json_encode($data);
    $signature = signPayload($payload, 'test_webhook_secret');

    $response = $this->call('POST', '/api/webhooks/razorpay', [], [], [], [
        'HTTP_X-Razorpay-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $response->assertSuccessful()
        ->assertJson(['status' => 'already_processed']);

    // Payment ID should NOT be overwritten
    $transaction->refresh();
    expect($transaction->payment_id)->toBe('pay_existing');
});

test('payment.failed webhook marks transaction as failed', function () {
    $transaction = Transaction::factory()->create([
        'razorpay_order_id' => 'order_fail123',
        'payment_status' => PaymentStatus::Pending,
    ]);

    $data = validWebhookPayload('payment.failed', [
        'order_id' => 'order_fail123',
    ]);
    $payload = json_encode($data);
    $signature = signPayload($payload, 'test_webhook_secret');

    $this->call('POST', '/api/webhooks/razorpay', [], [], [], [
        'HTTP_X-Razorpay-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $transaction->refresh();
    expect($transaction->payment_status)->toBe(PaymentStatus::Failed);
});

test('refund.created webhook marks transaction as refunded', function () {
    $transaction = Transaction::factory()->create([
        'payment_id' => 'pay_rfnd123',
        'payment_status' => PaymentStatus::Paid,
    ]);

    $data = validWebhookPayload('refund.created', [
        'id' => 'rfnd_test123',
        'payment_id' => 'pay_rfnd123',
    ]);
    $payload = json_encode($data);
    $signature = signPayload($payload, 'test_webhook_secret');

    $this->call('POST', '/api/webhooks/razorpay', [], [], [], [
        'HTTP_X-Razorpay-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $transaction->refresh();
    expect($transaction->payment_status)->toBe(PaymentStatus::Refunded)
        ->and($transaction->refund_id)->toBe('rfnd_test123');
});

test('transfer.failed webhook notifies admin users', function () {
    Notification::fake();

    // Create the Super Admin role
    \Spatie\Permission\Models\Role::create(['name' => 'Super Admin']);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $transaction = Transaction::factory()->create([
        'transfer_id' => 'trf_notify123',
    ]);

    $data = validWebhookPayload('transfer.failed', [
        'id' => 'trf_notify123',
        'error' => ['description' => 'KYC incomplete'],
    ]);
    $payload = json_encode($data);
    $signature = signPayload($payload, 'test_webhook_secret');

    $this->call('POST', '/api/webhooks/razorpay', [], [], [], [
        'HTTP_X-Razorpay-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    Notification::assertSentTo($admin, TransferFailedNotification::class);
});

test('webhook ignores unknown events gracefully', function () {
    $data = ['event' => 'order.paid', 'payload' => []];
    $payload = json_encode($data);
    $signature = signPayload($payload, 'test_webhook_secret');

    $response = $this->call('POST', '/api/webhooks/razorpay', [], [], [], [
        'HTTP_X-Razorpay-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $response->assertSuccessful()
        ->assertJson(['status' => 'ignored']);
});
