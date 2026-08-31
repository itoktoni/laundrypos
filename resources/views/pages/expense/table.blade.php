<?php /** @var App\Models\Expense $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        <x-table>
            <x-slot:head>
                <th>Actions</th>
                <x-table-sort field="expense_nama" label="Nama" />
                <th>Kategori</th>
                <x-table-sort field="expense_nominal" label="Nominal" />
                <x-table-sort field="expense_tanggal" label="Tanggal" />
                <th>Metode</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td>{{ $table->expense_nama }}</td>
                        <td><span class="badge badge-soft badge-info">{{ $table->expense_kategori }}</span></td>
                        <td class="font-mono">{{ formatAngka($table->expense_nominal, 'Rp ') }}</td>
                        <td>{{ formatDate($table->expense_tanggal) }}</td>
                        <td>{{ ucfirst($table->expense_metode_pembayaran) }}</td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>

        <x-pagination :paginator="$data" />
        <x-action :model="$model" :action="['create', 'delete']"/>
    </div>
</x-layouts::app>
