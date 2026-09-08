<?php

namespace App\Http\Middleware;

use App\Models\Laundry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureLaundrySelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $laundryId = session('laundry_id');
        $user = $request->user();

        if ($user === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        // Jika session valid, lanjut — tidak perlu auto-select.
        $isMember = $laundryId && DB::table('laundry_user')
            ->where('user_id', $user->id)
            ->where('laundry_id', $laundryId)
            ->exists();

        // owner/developer/admin dianggap member semua cabang aktif (tidak lewat pivot)
        $isPrivileged = in_array($user->role ?? '', ['owner', 'developer', 'admin'], true);
        if ($isPrivileged && $laundryId && Laundry::where('laundry_id', $laundryId)->where('laundry_is_aktif', true)->exists()) {
            return $next($request);
        }
        if ($isMember) {
            return $next($request);
        }

        // Session tidak valid/membership hilang — coba auto-select jika hanya 1 cabang tersedia.
        // Ini memenuhi request: "ketika login, dan cabang ada 1, otomatis login sebagai cabang tersebut"
        $accessibleIds = $isPrivileged
            ? Laundry::where('laundry_is_aktif', true)->orderBy('laundry_id')->pluck('laundry_id')->all()
            : DB::table('laundry_user')
                ->join('laundry', 'laundry.laundry_id', '=', 'laundry_user.laundry_id')
                ->where('laundry_user.user_id', $user->id)
                ->where('laundry.laundry_is_aktif', true)
                ->orderBy('laundry.laundry_id')
                ->pluck('laundry.laundry_id')
                ->all();

        // ponytail: jika hanya 1 cabang yang bisa diakses user ini (atau cuma 1 cabang aktif di sistem
        // untuk privileged), langsung set session tanpa lempar ke picker.
        if (count($accessibleIds) === 1) {
            session(['laundry_id' => (int) $accessibleIds[0]]);

            return $next($request);
        }

        // Lebih dari 1 atau 0 cabang — wajib pilih manual.
        session()->forget('laundry_id');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Laundry not selected'], 403);
        }

        return redirect()->route('laundry.picker');
    }
}
