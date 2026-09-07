<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class MesinJenisEnum extends Enum
{
    use EnumTrait;

    const WASHER = 'washer';

    const DRYER = 'dryer';

    const SETRIKA = 'setrika';

    const BOILER = 'boiler';

    const LAINNYA = 'lainnya';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::WASHER => 'Mesin Cuci (Washer)',
            self::DRYER => 'Pengering (Dryer)',
            self::SETRIKA => 'Setrika Uap',
            self::BOILER => 'Boiler',
            self::LAINNYA => 'Lainnya',
            default => parent::getDescription($value),
        };
    }
}
