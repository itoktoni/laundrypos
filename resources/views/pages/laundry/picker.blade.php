<x-layouts::app>
    <x-breadcrumb :items="[['url' => '', 'label' => 'Pilih Laundry']]" />
    <div class="content mt-4 lg:mt-0">
        <x-card label="Pilih Laundry">
            <form method="POST" action="{{ route('laundry.select') }}">
                @csrf
                <div class="space-y-4">
                    @foreach ($laundries as $laundry)
                        <label class="flex items-center gap-3 p-3 border border-outline-variant rounded-xl cursor-pointer hover:bg-surface-container">
                            <input type="radio" name="laundry_id" value="{{ $laundry->laundry_id }}" required class="radio radio-primary">
                            <span class="font-medium">{{ $laundry->laundry_nama }}</span>
                            <span class="text-xs text-on-surface-variant ml-auto">{{ $laundry->laundry_kode }}</span>
                        </label>
                    @endforeach

                    @if ($laundries->isEmpty())
                        <p class="text-sm text-on-surface-variant">Tidak ada laundry yang tersedia untuk akun Anda.</p>
                    @endif
                </div>

                @if ($laundries->isNotEmpty())
                    <button type="submit" class="btn btn-primary mt-4">Masuk</button>
                @endif
            </form>
        </x-card>
    </div>
</x-layouts::app>
