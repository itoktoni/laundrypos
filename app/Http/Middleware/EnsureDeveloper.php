<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeveloper
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ($user->role ?? '') !== 'developer') {
            abort(403, 'Unauthorized. Hanya Developer yang boleh mengakses pengaturan Website.');
        }

        return $next($request);
    }
}
