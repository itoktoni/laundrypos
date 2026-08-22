<?php

namespace App\Models;

use App\Enums\MetodePembayaranEnum;
use App\Enums\MetodePengambilanEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'order_code', 'order_id_customer', 'order_walkin_nama', 'order_walkin_telepon', 'order_id_user',
    'order_metode_pengambilan', 'order_alamat_jemput', 'order_slot_waktu', 'order_metode_pembayaran',
    'order_catatan', 'order_subtotal', 'order_total', 'order_status_id', 'order_estimasi_selesai',
])]
class Order extends BaseModel
{
    protected $table = 'order';

    protected $primaryKey = 'order_id';

    public static $filterColumns = ['order_code'];

    public static $sortColumns = ['order_code', 'created_at'];

    public static function field_name(): string
    {
        return 'order_code';
    }

    protected function casts(): array
    {
        return [
            'order_metode_pengambilan' => MetodePengambilanEnum::class,
            'order_metode_pembayaran' => MetodePembayaranEnum::class,
            'order_slot_waktu' => 'datetime',
            'order_estimasi_selesai' => 'datetime',
            'order_subtotal' => 'decimal:2',
            'order_total' => 'decimal:2',
        ];
    }

    /**
     * Generate the next order code for today, e.g. LDY-20260822-0001.
     * Counter resets daily; caller must retry on unique collision.
     */
    public static function generateCode(): string
    {
        $prefix = 'LDY-'.now()->format('Ymd').'-';

        $last = self::query()->lockForUpdate()
            ->where('order_code', 'like', $prefix.'%')
            ->max('order_code');

        $seq = $last ? ((int) substr((string) $last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function hasCustomer()
    {
        return $this->belongsTo(Customer::class, 'order_id_customer', 'customer_id');
    }

    public function hasUser()
    {
        return $this->belongsTo(User::class, 'order_id_user', 'id');
    }

    public function hasStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id', 'order_status_id');
    }

    public function hasItems()
    {
        return $this->hasMany(OrderItem::class, 'order_item_id_order', 'order_id');
    }

    public function hasStatusLogs()
    {
        return $this->hasMany(OrderStatusLog::class, 'order_status_log_id_order', 'order_id')->orderBy('created_at');
    }
}
