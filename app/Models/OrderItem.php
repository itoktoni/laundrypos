<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['order_item_id_order', 'order_item_id_product', 'order_item_nama_product', 'order_item_satuan', 'order_item_harga', 'order_item_qty', 'order_item_subtotal'])]
class OrderItem extends BaseModel
{
    protected $table = 'order_item';

    protected $primaryKey = 'order_item_id';

    public static $sortColumns = ['created_at'];

    protected function casts(): array
    {
        return [
            'order_item_harga' => 'decimal:2',
            'order_item_subtotal' => 'decimal:2',
            'order_item_qty' => 'integer',
        ];
    }

    public function hasOrder()
    {
        return $this->belongsTo(Order::class, 'order_item_id_order', 'order_id');
    }

    public function hasProduct()
    {
        return $this->belongsTo(Product::class, 'order_item_id_product', 'product_id');
    }
}
