<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['order_status_log_id_order', 'order_status_log_id_from', 'order_status_log_id_to', 'order_status_log_id_user', 'order_status_log_keterangan'])]
class OrderStatusLog extends BaseModel
{
    protected $table = 'order_status_log';

    protected $primaryKey = 'order_status_log_id';

    public static $sortColumns = ['created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function hasOrder()
    {
        return $this->belongsTo(Order::class, 'order_status_log_id_order', 'order_id');
    }

    public function hasFromStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_log_id_from', 'order_status_id');
    }

    public function hasToStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_log_id_to', 'order_status_id');
    }

    public function hasUser()
    {
        return $this->belongsTo(User::class, 'order_status_log_id_user', 'id');
    }
}
