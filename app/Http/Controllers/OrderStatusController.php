<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\OrderStatus;

class OrderStatusController extends Controller
{
    use ControllerTrait;

    public function __construct(OrderStatus $model)
    {
        $this->model = $model::getModel();
    }
}
