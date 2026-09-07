<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class InventoryTipeEnum extends Enum
{
    use EnumTrait;

    const MASUK = 'masuk';

    const KELUAR = 'keluar';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::MASUK => 'Masuk',
            self::KELUAR => 'Keluar',
            default => parent::getDescription($value),
        };
    }
}
