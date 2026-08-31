<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orders' => 'required|array|min:1',
            'orders.*.id' => 'required|string|max:36',
            'orders.*.customer_id' => 'nullable|integer',
            'orders.*.items' => 'required|array|min:1',
            'orders.*.items.*.product_id' => 'required|integer',
            'orders.*.items.*.product_nama' => 'required|string|max:100',
            'orders.*.items.*.qty' => 'required|integer|min:1',
            'orders.*.items.*.price' => 'required|numeric|min:0',
            'orders.*.total' => 'required|numeric|min:0',
            'orders.*.discount' => 'nullable|numeric|min:0',
            'orders.*.payment_method' => 'nullable|string|max:20',
            'orders.*.notes' => 'nullable|string|max:500',
            'orders.*.created_at' => 'required|date',
        ];
    }
}
