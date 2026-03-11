<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('app.payment_default_gateway', 'razorpay');
    }

    public function createRazorpayDriver(): PaymentGateway
    {
        return new RazorpayGateway;
    }
}
