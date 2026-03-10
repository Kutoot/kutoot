<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LoanStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Completed = 'completed';
    case Defaulted = 'defaulted';
    case Paused = 'paused';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Defaulted => 'Defaulted',
            self::Paused => 'Paused',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Completed => 'info',
            self::Defaulted => 'danger',
            self::Paused => 'warning',
        };
    }
}
