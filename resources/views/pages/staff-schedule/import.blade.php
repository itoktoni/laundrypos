<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => 'Import CSV']]" />

    <x-form :action="route('staff-schedule.postImport')" enctype="multipart/form-data">
        <x-card label="Import Jadwal CSV" icon="upload">
            <div class="col-span-12">
                <div class="rounded-xl border border-outline-variant bg-surface-container-low p-4">
                    <p class="font-body-sm text-body-sm font-bold text-on-surface mb-2 flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">description</span> Format kolom</p>
                    <p class="font-mono text-xs bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2 break-words">email,tanggal,jam_masuk,jam_pulang</p>
                    <div class="text-xs text-on-surface-variant mt-3 space-y-1">
                        <p>&bull; <b>email</b> — email karyawan terdaftar.</p>
                        <p>&bull; <b>tanggal</b> — YYYY-MM-DD (terima juga DD/MM/YYYY).</p>
                        <p>&bull; <b>jam</b> — format JJ:MM, contoh 08:00 dan 20:00.</p>
                        <p>&bull; Satu baris = satu tanggal kerja karyawan. Data lama (cabang, user, tanggal sama) diperbarui.</p>
                    </div>
                    <a href="{{ route('staff-schedule.getTemplate') }}" class="inline-flex items-center gap-2 h-9 px-4 text-sm font-semibold rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all mt-3">
                        <span class="material-symbols-outlined text-[18px]">download</span> Unduh template
                    </a>
                </div>
            </div>

            <x-file col="12" name="file" label="File CSV" accept=".csv,.txt" helper="Pilih file .csv / .txt — header: email,tanggal,jam_masuk,jam_pulang" />
        </x-card>

        <div class="action-bar fixed left-0 right-0 lg:left-72 bg-surface-container-lowest border-t border-outline-variant shadow-[0_-4px_12px_rgba(0,0,0,0.08)] px-3 md:px-6 py-2 md:py-3 z-[45]" style="bottom: 4rem">
            <div class="flex items-center justify-between max-w-full mx-auto gap-2 md:gap-3">
                <div></div>
                <div class="flex items-center gap-1.5 md:gap-3">
                    <a href="{{ moduleRoute('getTable') }}" wire:navigate class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all">
                        <span class="material-symbols-outlined text-base md:text-xl">close</span>
                        <span class="hidden sm:inline">Batal</span>
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95">
                        <span class="material-symbols-outlined text-base md:text-xl">upload</span>
                        <span class="hidden sm:inline">Upload & Import</span>
                        <span class="sm:hidden">Import</span>
                    </button>
                </div>
            </div>
        </div>
    </x-form>
</x-layouts::app>
