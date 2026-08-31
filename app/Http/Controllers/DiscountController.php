<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Discount;

class DiscountController extends Controller
{
    use ControllerTrait;

    public function __construct(Discount $model)
    {
        $this->model = $model::getModel();
    }
}
