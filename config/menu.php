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
            ],
        ],
        [
            'label' => 'Master Data',
            'items' => [
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
