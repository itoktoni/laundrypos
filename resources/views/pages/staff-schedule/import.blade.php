<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => 'Import CSV']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex flex-col gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="font-headline-lg-mobile sm:font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight">Import Jadwal CSV</h1>
                <p class="text-body-sm text-on-surface-variant">Satu baris = satu tanggal kerja karyawan. Data lama (cabang, user, tanggal sama) diperbarui.</p>
            </div>

            <div class="bg-surface-container-low rounded-xl border border-outline-variant p-4">
                <p class="text-body-sm font-bold text-on-surface mb-2">Format kolom</p>
                <p class="text-body-xs text-on-surface-variant font-mono break-words">email,tanggal,jam_masuk,jam_pulang</p>
                <div class="text-body-xs text-on-surface-variant mt-2 space-y-1">
                    <p>&bull; <b>email</b> — email karyawan terdaftar.</p>
                    <p>&bull; <b>tanggal</b> — YYYY-MM-DD (terima juga DD/MM/YYYY).</p>
                    <p>&bull; <b>jam</b> — format JJ:MM, contoh 08:00 dan 20:00.</p>
                </div>
                <a href="{{ route('staff-schedule.getTemplate') }}" class="btn btn-sm btn-soft mt-3 gap-1"><span class="material-symbols-outlined text-sm">download</span> Unduh template</a>
            </div>

            <form method="POST" action="{{ route('staff-schedule.postImport') }}" enctype="multipart/form-data" class="bg-surface-container-low rounded-xl border border-outline-variant p-4 flex flex-col gap-3">
                @csrf
                <label class="text-body-xs text-on-surface-variant">File CSV (maks 2MB)
                    <input type="file" name="file" accept=".csv,.txt" required class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                </label>
                @error('file')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn btn-sm btn-primary sm:self-start">Upload &amp; Import</button>
            </form>
        </div>
    </div>
</x-layouts::app>
