<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TargetType: string implements HasLabel
{
    case Amount = 'amount';
    case TransactionCount = 'transaction_count';

    public function getLabel(): string
    {
        return match ($this) {
            self::Amount => 'Amount (₹)',
            self::TransactionCount => 'Transaction Count',
        };
    }
}
