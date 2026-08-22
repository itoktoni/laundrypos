<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['customer_nama', 'customer_telepon', 'customer_email', 'customer_alamat'])]
class Customer extends BaseModel
{
    use BelongsToLaundry;

    protected $table = 'customer';

    protected $primaryKey = 'customer_id';

    public static $filterColumns = ['customer_nama', 'customer_telepon', 'customer_email'];

    public static $sortColumns = ['customer_nama', 'customer_telepon'];

    public static function field_name(): string
    {
        return 'customer_nama';
    }

    public function rules(): array
    {
        return [
            'customer_nama' => ['required', 'string', 'max:100'],
            'customer_telepon' => [
                'required', 'string', 'max:15',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! preg_match('/^[0-9]{8,15}$/', (string) $value)) {
                        $fail('Nomor telepon harus 8-15 digit angka.');

                        return;
                    }

                    $exists = Customer::query()
                        ->where('customer_telepon', $value)
                        ->when(request()->route('id'), fn ($q, $id) => $q->where('customer_id', '!=', $id))
                        ->exists();

                    if ($exists) {
                        $fail('Nomor telepon sudah terdaftar dalam sistem.');
                    }
                },
            ],
            'customer_email' => ['nullable', 'email', 'max:254'],
            'customer_alamat' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function hasOrders()
    {
        return $this->hasMany(Order::class, 'order_id_customer', 'customer_id');
    }
}
