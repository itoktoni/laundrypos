<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ControllerTrait;

    public function __construct(Order $model)
    {
        $this->model = $model::getModel();
    }

    protected function getData()
    {
        return $this->model->with(['hasItems', 'hasStatus'])->orderByDesc('created_at');
    }

    public function getShow(Request $request, $id)
    {
        $order = $this->model->with(['hasItems', 'hasStatus', 'hasStatusLogs.hasToStatus', 'hasStatusLogs.hasFromStatus', 'hasCustomer'])->findOrFail($id);

        return $this->views('pages.order.show', [
            'order' => $order,
        ]);
    }
}
