<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class MesinStatusEnum extends Enum
{
    use EnumTrait;

    const AKTIF = 'aktif';

    const RUSAK = 'rusak';

    const MAINTENANCE = 'maintenance';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::AKTIF => 'Aktif',
            self::RUSAK => 'Rusak',
            self::MAINTENANCE => 'Maintenance',
            default => parent::getDescription($value),
        };
    }
}
