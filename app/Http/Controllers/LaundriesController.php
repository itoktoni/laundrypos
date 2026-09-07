<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Laundry;

class LaundriesController extends Controller
{
    use ControllerTrait;

    public function __construct(Laundry $model)
    {
        $this->model = $model::getModel();
    }

    protected function getData()
    {
        // Owner sees all, others see only their assigned (but laundry management typically owner only)
        return $this->model->orderBy('laundry_nama');
    }
}
