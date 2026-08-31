<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class MetodePengambilanEnum extends Enum
{
    use EnumTrait;

    const ANTAR_TOKO = 'antar_toko';

    const JEMPUT = 'jemput';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::ANTAR_TOKO => 'Antar ke Toko',
            self::JEMPUT => 'Dijemput',
            default => parent::getDescription($value),
        };
    }
}
