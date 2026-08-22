<?php

namespace Tests\Feature;

use App\Models\BaseModel;
use App\Models\Laundry;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TenantDummy extends BaseModel
{
    use \App\Concerns\BelongsToLaundry;

    protected $table = 'tenant_dummies';
    protected $primaryKey = 'tenant_dummy_id';

    #[Fillable(['tenant_dummy_nama'])]
    protected $guarded = [];
}

it('scopes and auto-assigns laundry_id from session', function () {
    Schema::dropIfExists('tenant_dummies');
    Schema::create('tenant_dummies', function (Blueprint $table) {
        $table->id('tenant_dummy_id');
        $table->unsignedBigInteger('tenant_dummies_id_laundry')->nullable();
        $table->string('tenant_dummy_nama');
        $table->timestamps();
    });

    $a = Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    $b = Laundry::create(['laundry_nama' => 'B', 'laundry_kode' => 'B']);

    session(['laundry_id' => $a->laundry_id]);
    TenantDummy::create(['tenant_dummy_nama' => 'in-a']);

    session(['laundry_id' => $b->laundry_id]);
    TenantDummy::create(['tenant_dummy_nama' => 'in-b']);

    expect(TenantDummy::count())->toBe(1)
        ->and(TenantDummy::first()->tenant_dummy_nama)->toBe('in-b')
        ->and(TenantDummy::withoutGlobalScopes()->count())->toBe(2);

    Schema::dropIfExists('tenant_dummies');
});
