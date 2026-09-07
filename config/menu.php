<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu Configuration
    |--------------------------------------------------------------------------
    |
    | Define menu items for desktop sidebar, mobile drawer, and bottom nav.
    | Each item: route (string), icon (string), label (string)
    | Sections: label (string), items (array)
    | Bottom nav: only 5 items max, uses short label
    |
    */

    'sidebar' => [
        [
            'label' => null,
            'items' => [
                ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
            ],
        ],
        [
            'label' => 'Laundry',
            'items' => [
                ['route' => 'pos.index', 'icon' => 'point_of_sale', 'label' => 'POS Kasir'],
                ['route' => 'order.getTable', 'icon' => 'receipt_long', 'label' => 'Orders', 'match' => ['order.*']],
                ['route' => 'crm.dashboard', 'icon' => 'analytics', 'label' => 'CRM', 'match' => ['crm.*']],
                ['route' => 'expense.getTable', 'icon' => 'receipt_long', 'label' => 'Pengeluaran', 'match' => ['expense.*']],
                ['route' => 'inventory.getTable', 'icon' => 'inventory_2', 'label' => 'Inventory', 'match' => ['inventory.*']],
                ['route' => 'inventory-movement.getTable', 'icon' => 'swap_horiz', 'label' => 'Stok Masuk/Keluar', 'match' => ['inventory-movement.*']],
                ['route' => 'mesin.getTable', 'icon' => 'hardware', 'label' => 'Mesin', 'match' => ['mesin.*']],
                ['route' => 'mesin-service.getTable', 'icon' => 'handyman', 'label' => 'Service Mesin', 'match' => ['mesin-service.*']],
                ['route' => 'staff-attendance.getCheckin', 'icon' => 'how_to_reg', 'label' => 'Absensi', 'match' => ['staff-attendance.*']],
            ],
        ],
        [
            'label' => 'Laporan',
            'items' => [
                ['route' => 'report.order.getIndex', 'icon' => 'receipt_long', 'label' => 'Order', 'match' => ['report.order.*']],
                ['route' => 'report.expense.getIndex', 'icon' => 'payments', 'label' => 'Pengeluaran', 'match' => ['report.expense.*']],
                ['route' => 'report.inventory.getIndex', 'icon' => 'inventory_2', 'label' => 'Inventory', 'match' => ['report.inventory.*']],
                ['route' => 'report.mesin.getIndex', 'icon' => 'hardware', 'label' => 'Mesin', 'match' => ['report.mesin.*']],
                ['route' => 'report.absensi.getIndex', 'icon' => 'how_to_reg', 'label' => 'Absensi', 'match' => ['report.absensi.*']],
                ['route' => 'report.penggajian.getIndex', 'icon' => 'account_balance_wallet', 'label' => 'Penggajian', 'match' => ['report.penggajian.*']],
            ],
        ],
        [
            'label' => 'Master Data',
            'items' => [
                ['route' => 'laundries.getTable', 'icon' => 'storefront', 'label' => 'Cabang', 'match' => ['laundries.*']],
                ['route' => 'product.getTable', 'icon' => 'local_laundry_service', 'label' => 'Products', 'match' => ['product.*']],
                ['route' => 'kategori.getTable', 'icon' => 'category', 'label' => 'Kategori', 'match' => ['kategori.*']],
                ['route' => 'discount.getTable', 'icon' => 'sell', 'label' => 'Discount', 'match' => ['discount.*']],
                ['route' => 'customer.getTable', 'icon' => 'people', 'label' => 'Customers', 'match' => ['customer.*']],
                ['route' => 'order-status.getTable', 'icon' => 'flag', 'label' => 'Status Order', 'match' => ['order-status.*']],
                ['route' => 'user.getTable', 'icon' => 'manage_accounts', 'label' => 'Users', 'match' => ['user.*']],
            ],
        ],
        [
            'label' => 'CMS',
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
                ['route' => 'settings.website', 'icon' => 'language', 'label' => 'Website'],
                ['route' => 'native-bridge-test', 'icon' => 'phone_android', 'label' => 'NativeBridge Test'],
            ],
        ],
    ],

    'bottom_nav' => [
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Home'],
        ['route' => 'pos.index', 'icon' => 'point_of_sale', 'label' => 'POS'],
        ['route' => 'order.getTable', 'icon' => 'receipt_long', 'label' => 'Orders'],
        ['route' => 'customer.getTable', 'icon' => 'people', 'label' => 'Customers'],
        ['route' => 'settings.website', 'icon' => 'settings', 'label' => 'Settings'],
    ],

];
