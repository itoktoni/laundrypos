<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use App\Enums\MetodePembayaranEnum;
use App\Enums\MetodePengambilanEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'order_code', 'order_id_customer', 'order_walkin_nama', 'order_walkin_telepon', 'order_id_user',
    'order_metode_pengambilan', 'order_alamat_jemput', 'order_slot_waktu', 'order_metode_pembayaran',
    'order_catatan', 'order_subtotal', 'order_total', 'order_status_id', 'order_estimasi_selesai',
])]
class Order extends BaseModel
{
    use BelongsToLaundry;

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

    /**
     * Transition the order to another status following the laundry's flow:
     * forward one step at a time, or cancellation from any non-terminal status
     * with a mandatory reason.
     */
    public function transitStatus(OrderStatus $to, ?string $keterangan = null): void
    {
        $current = $this->hasStatus;

        if ($current && $current->order_status_is_selesai) {
            throw ValidationException::withMessages([
                'status' => ['Order sudah selesai, status tidak dapat diubah.'],
            ]);
        }

        if ($to->order_status_is_batal) {
            if ($keterangan === null || trim($keterangan) === '') {
                throw ValidationException::withMessages([
                    'keterangan' => ['Alasan pembatalan wajib diisi.'],
                ]);
            }
        } else {
            $statuses = OrderStatus::get()->values();
            $currentIndex = $statuses->search(fn ($s) => $s->getKey() === $current?->getKey());
            $targetIndex = $statuses->search(fn ($s) => $s->getKey() === $to->getKey());

            if ($currentIndex === false || $targetIndex !== $currentIndex + 1) {
                $nextName = ($currentIndex !== false && isset($statuses[$currentIndex + 1]))
                    ? $statuses[$currentIndex + 1]->order_status_nama
                    : '-';

                throw ValidationException::withMessages([
                    'status' => ["Perpindahan status tidak valid. Status berikutnya yang diizinkan: {$nextName}"],
                ]);
            }
        }

        if (mb_strlen((string) $keterangan) > 255) {
            throw ValidationException::withMessages([
                'keterangan' => ['Keterangan maksimal 255 karakter.'],
            ]);
        }

        DB::transaction(function () use ($to, $keterangan): void {
            OrderStatusLog::create([
                'order_status_log_id_order' => $this->getKey(),
                'order_status_log_id_from' => $this->order_status_id,
                'order_status_log_id_to' => $to->getKey(),
                'order_status_log_id_user' => auth()->id(),
                'order_status_log_keterangan' => $to->order_status_is_batal ? $keterangan : null,
            ]);

            $this->update(['order_status_id' => $to->getKey()]);
        });
    }
}
