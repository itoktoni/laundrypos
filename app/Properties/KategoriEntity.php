<?php

namespace App\Properties;

trait KategoriEntity
{
    public static function field_nama()
    {
        return 'kategori_nama';
    }

    public static function field_is_aktif()
    {
        return 'kategori_is_aktif';
    }

    public function getFieldNamaAttribute()
    {
        return $this->{static::field_nama()};
    }
}
