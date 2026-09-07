<?php

namespace App\Http\Controllers;

use App\Models\Laundry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaundryController extends Controller
{
    public function picker(Request $request)
    {
        $role = Auth::user()->role ?? '';

        // ponytail: jangan auto-select di sini — owner yang explisit buka
        // picker (mis. via "Ganti Cabang") harus bisa pindah cabang.
        // Auto-select saat session kosong ditangani EnsureLaundrySelected.
        $laundries = $role === 'owner'
            ? Laundry::where('laundry_is_aktif', true)->orderBy('laundry_nama')->get()
            : Laundry::whereIn('laundry_id', $this->pivotIds())->orderBy('laundry_nama')->get();

        return view('pages.laundry.picker', [
            'laundries' => $laundries,
        ]);
    }

    public function select(Request $request)
    {
        $validated = $request->validate([
            'laundry_id' => ['required', 'integer'],
        ]);

        $laundry = Laundry::find($validated['laundry_id']);
        abort_unless($laundry, 404);

        // ponytail: non-owner hanya boleh pilih cabang anggotanya.
        $role = Auth::user()->role ?? '';
        if ($role !== 'owner' && ! in_array($laundry->laundry_id, $this->pivotIds())) {
            abort(403, 'Bukan anggota cabang ini.');
        }

        session(['laundry_id' => (int) $validated['laundry_id']]);

        return redirect()->route('dashboard');
    }

    private function pivotIds(): array
    {
        return DB::table('laundry_user')
            ->where('user_id', Auth::id())
            ->pluck('laundry_id')
            ->all();
    }
}
