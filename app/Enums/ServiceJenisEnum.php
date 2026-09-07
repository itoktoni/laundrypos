<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class ServiceJenisEnum extends Enum
{
    use EnumTrait;

    const RUTIN = 'rutin';

    const PERBAIKAN = 'perbaikan';

    const DARURAT = 'darurat';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::RUTIN => 'Service Rutin',
            self::PERBAIKAN => 'Perbaikan',
            self::DARURAT => 'Darurat',
            default => parent::getDescription($value),
        };
    }
}
