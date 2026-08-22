<?php /** @var App\Models\Order $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        <x-filter :per-page="25" :fields="$fields">
            <x-slot:advanced>
                @foreach ($fields as $key => $advance)
                    <x-filter-item :label="$advance" :name="$key"/>
                @endforeach

                <x-button variant="primary" class="btn-block" onclick="applyAdvanced()">Apply</x-button>
                <x-button variant="soft" class="btn-block" onclick="resetAdvanced()">Reset</x-button>
            </x-slot:advanced>
        </x-filter>

        <x-table>
            <x-slot:head>
                <th>Actions</th>
                <th>Kode</th>
                <th>Status</th>
                <th>Total</th>
                <th>Tanggal</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td><a class="link" href="{{ route('order.getShow', ['id' => $table->field_primary]) }}">{{ $table->order_code }}</a></td>
                        <td>
                            <span class="badge badge-outline" style="border-color: {{ $table->hasStatus?->order_status_warna }}">
                                {{ $table->hasStatus?->order_status_nama ?? '-' }}
                            </span>
                        </td>
                        <td>{{ formatAngka($table->order_total) }}</td>
                        <td>{{ formatDate($table->created_at) }}</td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>

        {{ $data->links() }}
    </div>
</x-layouts::app>
