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

        // Owner has 1 laundry — auto-select, no picker.
        if (($user->role ?? '') === 'owner') {
            if (! $laundryId || ! Laundry::find($laundryId)) {
                $laundry = Laundry::where('laundry_is_aktif', true)->first();

                if ($laundry) {
                    session(['laundry_id' => $laundry->laundry_id]);

                    return $next($request);
                }

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Laundry not selected'], 403);
                }

                return redirect()->route('laundry.picker');
            }

            return $next($request);
        }

        $isMember = $laundryId && DB::table('laundry_user')
            ->where('user_id', $user->id)
            ->where('laundry_id', $laundryId)
            ->exists();

        if (! $isMember) {
            session()->forget('laundry_id');

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Laundry not selected'], 403);
            }

            return redirect()->route('laundry.picker');
        }

        return $next($request);
    }
}
