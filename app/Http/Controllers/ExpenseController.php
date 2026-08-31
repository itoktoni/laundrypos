<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Expense;

class ExpenseController extends Controller
{
    use ControllerTrait;

    public function __construct(Expense $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'kategoriOptions' => Expense::kategoriOptions(),
            'metodeOptions' => [
                'tunai' => 'Tunai',
                'transfer' => 'Transfer',
                'dompet_digital' => 'Dompet Digital',
            ],
        ], $data);
    }
}
