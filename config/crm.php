<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CRM Configuration
    |--------------------------------------------------------------------------
    |
    | Threshold settings for customer analytics and CRM metrics.
    |
    */

    // Jumlah minimum order untuk jadi VIP
    'vip_threshold' => (int) env('CRM_VIP_THRESHOLD', 10),

    // Hari tanpa order dianggap churned (tidak kembali)
    'churn_days' => (int) env('CRM_CHURN_DAYS', 30),

    // Hari untuk anggap customer baru
    'new_customer_days' => (int) env('CRM_NEW_CUSTOMER_DAYS', 30),

    // Hari untuk cek pertumbuhan customer (month over month)
    'growth_period_days' => (int) env('CRM_GROWTH_PERIOD_DAYS', 30),

];
