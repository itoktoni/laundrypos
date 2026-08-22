<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'POS Kasir']]" />
    <div class="content mt-4 lg:mt-0">
        <livewire:pos.pos-terminal />
    </div>
</x-layouts::app>
