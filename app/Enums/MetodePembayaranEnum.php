<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class MetodePembayaranEnum extends Enum
{
    use EnumTrait;

    const TUNAI = 'tunai';

    const TRANSFER = 'transfer';

    const DOMPET_DIGITAL = 'dompet_digital';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::TUNAI => 'Tunai',
            self::TRANSFER => 'Transfer',
            self::DOMPET_DIGITAL => 'Dompet Digital',
            default => parent::getDescription($value),
        };
    }
}
