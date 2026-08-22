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

        abort_unless(Laundry::find($validated['laundry_id']), 404);

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
