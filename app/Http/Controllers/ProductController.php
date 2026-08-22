<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Enums\SatuanEnum;
use App\Models\Kategori;
use App\Models\Product;

class ProductController extends Controller
{
    use ControllerTrait;

    public function __construct(Product $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'kategoriOptions' => Kategori::orderBy('kategori_nama')->get()->pluck('kategori_nama', 'kategori_id')->all(),
            'satuanOptions' => SatuanEnum::getOptions(),
        ], $data);
    }
}
