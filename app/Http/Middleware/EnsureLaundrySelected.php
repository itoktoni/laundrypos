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
            return redirect()->route('login');
        }

        // Owner has access to every laundry.
        if (($user->role ?? '') === 'owner') {
            if (! $laundryId || ! Laundry::find($laundryId)) {
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

            return redirect()->route('laundry.picker');
        }

        return $next($request);
    }
}
