<?php

namespace App\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait ReportTrait
{
    // ponytail: rentang tanggal default bulan berjalan; tukar bila terbalik.
    protected function periode(Request $request): array
    {
        $dari = $request->input('dari', now()->startOfMonth()->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());

        try {
            $dari = Carbon::parse($dari)->toDateString();
        } catch (\Throwable) {
            $dari = now()->startOfMonth()->toDateString();
        }
        try {
            $sampai = Carbon::parse($sampai)->toDateString();
        } catch (\Throwable) {
            $sampai = now()->toDateString();
        }
        if ($dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari, $sampai];
    }
}
