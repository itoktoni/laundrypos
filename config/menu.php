<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu Configuration — Role Based
    |--------------------------------------------------------------------------
    |
    | 'roles' => ['developer','admin','editor','user']
    | - Jika tidak ada key 'roles' => terlihat untuk semua role (public).
    | - Jika ada 'roles' di section => section + semua items di dalamnya
    |   hanya untuk role tersebut (kecuali item override sendiri).
    | - Jika ada 'roles' di item => override section, hanya role itu.
    |
    | RoleEnum: developer, admin, editor, user
    | - developer: superadmin, full access termasuk Website + blueprint CMS + keuangan & penggajian
    | - admin:     kasir/operasional, POS + Orders + Master Data non-user + Laporan operasional (tanpa penggajian/pengeluaran)
    | - editor:    konten & kasir, CMS content + POS/Orders/Customers
    | - user:      staff lapangan, POS + Absensi + Jadwal saja
    |
    */

    'sidebar' => [
        [
            'label' => null,
            'items' => [
                // Dashboard — semua role
                ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
            ],
        ],
        [
            'label' => 'Laundry',
            // Section Laundry — semua role bisa lihat, tapi item dibatasi per-role
            'items' => [
                ['route' => 'pos.index', 'icon' => 'point_of_sale', 'label' => 'POS Kasir', 'roles' => ['developer', 'admin', 'editor', 'user']],
                ['route' => 'order.getTable', 'icon' => 'receipt_long', 'label' => 'Orders', 'match' => ['order.*'], 'roles' => ['developer', 'admin', 'editor', 'user']],
                ['route' => 'crm.dashboard', 'icon' => 'analytics', 'label' => 'CRM', 'match' => ['crm.*'], 'roles' => ['developer', 'admin']],
                ['route' => 'expense.getTable', 'icon' => 'receipt_long', 'label' => 'Pengeluaran', 'match' => ['expense.*'], 'roles' => ['developer', 'admin']],
                ['route' => 'inventory.getTable', 'icon' => 'inventory_2', 'label' => 'Inventory', 'match' => ['inventory.*'], 'roles' => ['developer', 'admin']],
                ['route' => 'inventory-movement.getTable', 'icon' => 'swap_horiz', 'label' => 'Stok Masuk/Keluar', 'match' => ['inventory-movement.*'], 'roles' => ['developer', 'admin']],
                ['route' => 'mesin.getTable', 'icon' => 'hardware', 'label' => 'Mesin', 'match' => ['mesin.*'], 'roles' => ['developer', 'admin']],
                ['route' => 'mesin-service.getTable', 'icon' => 'handyman', 'label' => 'Service Mesin', 'match' => ['mesin-service.*'], 'roles' => ['developer', 'admin']],
                ['route' => 'staff-attendance.getCheckin', 'icon' => 'how_to_reg', 'label' => 'Absensi', 'match' => ['staff-attendance.*'], 'roles' => ['developer', 'admin', 'editor', 'user']],
                ['route' => 'staff-schedule.getTable', 'icon' => 'schedule', 'label' => 'Jadwal', 'match' => ['staff-schedule.*'], 'roles' => ['developer', 'admin', 'editor', 'user']],
            ],
        ],
        [
            'label' => 'Laporan',
            'roles' => ['developer', 'admin', 'owner'],
            'items' => [
                ['route' => 'report.order.getIndex', 'icon' => 'receipt_long', 'label' => 'Order', 'match' => ['report.order.*']],
                ['route' => 'report.expense.getIndex', 'icon' => 'payments', 'label' => 'Pengeluaran', 'match' => ['report.expense.*'], 'roles' => ['developer']],
                ['route' => 'report.inventory.getIndex', 'icon' => 'inventory_2', 'label' => 'Inventory', 'match' => ['report.inventory.*']],
                ['route' => 'report.mesin.getIndex', 'icon' => 'hardware', 'label' => 'Mesin', 'match' => ['report.mesin.*']],
                ['route' => 'report.absensi.getIndex', 'icon' => 'how_to_reg', 'label' => 'Absensi', 'match' => ['report.absensi.*']],
                ['route' => 'report.penggajian.getIndex', 'icon' => 'account_balance_wallet', 'label' => 'Penggajian', 'match' => ['report.penggajian.*'], 'roles' => ['developer']],
                ['route' => 'report.jadwal.getIndex', 'icon' => 'calendar_month', 'label' => 'Roster', 'match' => ['report.jadwal.*']],
            ],
        ],
        [
            'label' => 'Master Data',
            'roles' => ['developer', 'admin', 'owner'],
            'items' => [
                ['route' => 'laundries.getTable', 'icon' => 'storefront', 'label' => 'Cabang', 'match' => ['laundries.*'], 'roles' => ['owner']],
                ['route' => 'product.getTable', 'icon' => 'local_laundry_service', 'label' => 'Products', 'match' => ['product.*'], 'roles' => ['owner']],
                ['route' => 'kategori.getTable', 'icon' => 'category', 'label' => 'Kategori', 'match' => ['kategori.*'], 'roles' => ['owner']],
                ['route' => 'discount.getTable', 'icon' => 'sell', 'label' => 'Discount', 'match' => ['discount.*'], 'roles' => ['owner']],
                ['route' => 'customer.getTable', 'icon' => 'people', 'label' => 'Customers', 'match' => ['customer.*']],
                ['route' => 'order-status.getTable', 'icon' => 'flag', 'label' => 'Status Order', 'match' => ['order-status.*'], 'roles' => ['owner']],
                ['route' => 'user.getTable', 'icon' => 'manage_accounts', 'label' => 'Users', 'match' => ['user.*'], 'roles' => ['developer']],
            ],
        ],
        [
            'label' => 'CMS',
            'roles' => ['editor'],
            'items' => [
                ['route' => 'cms-type.getTable', 'icon' => 'category', 'label' => 'Types', 'match' => ['cms-type.*']],
                ['route' => 'field.getTable', 'icon' => 'input', 'label' => 'Fields', 'match' => ['field.*']],
                ['route' => 'section.getTable', 'icon' => 'view_agenda', 'label' => 'Sections', 'match' => ['section.*']],
                ['route' => 'content.getTable', 'icon' => 'article', 'label' => 'Content', 'match' => ['content.*']],
                ['route' => 'category.getTable', 'icon' => 'sell', 'label' => 'Categories', 'match' => ['category.*']],
                ['route' => 'tag.getTable', 'icon' => 'label', 'label' => 'Tags', 'match' => ['tag.*']],
                ['route' => 'menu.getTable', 'icon' => 'menu', 'label' => 'Menus', 'match' => ['menu.*']],
            ],
        ],
        [
            'label' => 'Settings',
            'items' => [
                ['route' => 'settings.website', 'icon' => 'language', 'label' => 'Website', 'roles' => ['developer']],
                ['route' => 'native-bridge-test', 'icon' => 'phone_android', 'label' => 'NativeBridge Test', 'roles' => ['developer', 'admin']],
            ],
        ],
    ],

    'bottom_nav' => [
        // Urutan global sengaja: Home, Order, POS (center), Customer/Settings (admin), Absen/Jadwal (user) — filter per role jadi 5 item pas
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Home', 'roles' => ['developer', 'admin', 'editor', 'user']],
        ['route' => 'order.getTable', 'icon' => 'receipt_long', 'label' => 'Orders', 'roles' => ['developer', 'admin', 'editor', 'user']],
        ['route' => 'pos.index', 'icon' => 'point_of_sale', 'label' => 'POS', 'roles' => ['developer', 'admin', 'editor', 'user']],
        ['route' => 'customer.getTable', 'icon' => 'people', 'label' => 'Customers', 'roles' => ['developer', 'admin']],
        ['route' => 'settings.website', 'icon' => 'settings', 'label' => 'Settings', 'roles' => ['developer', 'admin']],
        ['route' => 'staff-attendance.getCheckin', 'icon' => 'how_to_reg', 'label' => 'Absen', 'roles' => ['editor', 'user']],
        ['route' => 'staff-schedule.getTable', 'icon' => 'schedule', 'label' => 'Jadwal', 'roles' => ['editor', 'user']],
    ],

];
