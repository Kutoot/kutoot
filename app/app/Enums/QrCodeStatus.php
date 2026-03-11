<?php

namespace App\Enums;

enum QrCodeStatus: string
{
    case Available = 'available';
    case Linked = 'linked';
    case Deactivated = 'deactivated';
}
