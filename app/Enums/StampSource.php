<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StampSource: string implements HasLabel
{
    case PlanPurchase = 'plan_purchase';
    case BillPayment = 'bill_payment';

    public function getLabel(): string
    {
        return match ($this) {
            self::PlanPurchase => 'Plan Purchase',
            self::BillPayment => 'Bill Payment',
        };
    }
}
