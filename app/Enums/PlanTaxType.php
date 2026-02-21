<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PlanTaxType: string implements HasLabel
{
    case Inclusive = 'inclusive';
    case Exclusive = 'exclusive';
    case None = 'none';

    public function getLabel(): string
    {
        return match ($this) {
            self::Inclusive => 'Inclusive of GST',
            self::Exclusive => 'Exclusive of GST',
            self::None => 'No Tax',
        };
    }
}
