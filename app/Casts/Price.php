<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Price implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        return $value / 100;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return $value * 100;
    }
}
