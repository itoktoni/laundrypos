<?php

namespace App\Concerns;

use App\Models\Laundry;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToLaundry
{
    public static function bootBelongsToLaundry(): void
    {
        static::addGlobalScope('laundry', function (Builder $builder): void {
            $laundryId = session('laundry_id');
            if ($laundryId) {
                $builder->where($builder->getModel()->getTable().'_id_laundry', $laundryId);
            }
        });

        static::creating(function ($model): void {
            $column = $model->getTable().'_id_laundry';
            if (! $model->{$column} && session('laundry_id')) {
                $model->{$column} = session('laundry_id');
            }
        });
    }

    public function hasLaundry()
    {
        return $this->belongsTo(Laundry::class, $this->getTable().'_id_laundry', 'laundry_id');
    }
}
