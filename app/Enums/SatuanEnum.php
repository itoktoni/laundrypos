<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class SatuanEnum extends Enum
{
    use EnumTrait;

    const KG = 'kg';
    const ITEM = 'item';
    const PASANG = 'pasang';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::KG => 'Per Kg',
            self::ITEM => 'Per Item',
            self::PASANG => 'Per Pasang',
            default => parent::getDescription($value),
        };
    }
}
