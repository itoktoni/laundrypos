<?php

namespace App\Properties;

trait LaundryEntity
{
    public static function field_nama()
    {
        return 'laundry_nama';
    }

    public static function field_kode()
    {
        return 'laundry_kode';
    }

    public function getFieldNamaAttribute()
    {
        return $this->{static::field_nama()};
    }
}
