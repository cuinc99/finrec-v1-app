<?php

return [
    'users' => [
        'title' => 'Pengguna',
        'fields' => [
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'email_verified_at' => 'Email Terverifikasi',
            'password' => 'Password',
            'role' => 'Peran',
            'remember_token' => 'Token',
        ],
    ],
    'customers' => [
        'title' => 'Pelanggan',
        'fields' => [
            'name' => 'Nama',
            'type' => 'Tipe',
            'total_transaction' => 'Total Transaksi',
            'total_products_purchased' => 'Total Produk yang Dibeli',
            'total_buy' => 'Total Belanja',
        ],
        'links' => [
            'view' => 'Data Transaksi',
        ]
    ],
    'products' => [
        'title' => 'Produk',
        'fields' => [
            'name' => 'Nama',
            'description' => 'Deskripsi',
            'selling_price' => 'Harga',
            'sold' => 'Terjual',
        ],
    ],
    'transactions' => [
        'title' => 'Transaksi',
        'fields' => [
            'transaction_code' => 'Kode Transaksi',
            'purchase_date' => 'Tanggal Pembelian',
            'price' => 'Harga',
            'quantity' => 'Qty',
            'discount' => 'Diskon',
            'subtotal' => 'Subtotal',
            'subtotal_after_discount' => 'Subtotal Setelah Diskon',
            'product' => 'Produk',
            'customer' => 'Pelanggan',
            'profit' => 'Laba/Rugi',
            'total_sales' => 'Penjualan',
            'total_transactions' => 'Transaksi',
        ],
    ],
    'expenses' => [
        'title' => 'Pengeluaran',
        'fields' => [
            'expense_code' => 'Kode Pengeluaran',
            'purchase_date' => 'Tanggal Pembelian',
            'product' => 'Produk',
            'price' => 'Harga',
            'subtotal' => 'Subtotal',
        ],
    ],
    'widgets' => [
        'sales_expenses_per_month_chart' => [
            'heading' => 'Grafik Penjualan & Pengeluaran per Bulan',
            'heading_table' => 'Penjualan & Pengeluaran per Bulan',
            'datasets_label_sales' => 'Penjualan',
            'datasets_label_expenses' => 'Pengeluaran',
        ],
        'product_sold_per_month_chart' => [
            'heading' => 'Grafik Produk Terjual per Bulan',
            'heading_table' => 'Produk Terjual per Bulan',
            'datasets_label' => 'Produk',
        ],
    ],
    'common' => [
        'created_at' => 'Dibuat Pada',
        'updated_at' => 'Diperbarui Pada',
        'deleted_at' => 'Dihapus Pada',
        'actions' => 'Aksi',
        'create' => 'Buat',
        'view' => 'Lihat',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'save' => 'Simpan',
        'cancel' => 'Batal',
        'back' => 'Kembali',
        'confirm' => 'Konfirmasi',
        'yes' => 'Ya',
        'no' => 'Tidak',
        'all' => 'Semua',
        'money_locale' => 'idr',
        'created_from' => 'Dari tanggal',
        'created_until' => 'Sampai tanggal',
        'today' => 'Hari ini',
        'latest' => 'Terbaru',
        'total' => 'Total',
        'sold' => 'Terjual',
        'month' => 'Bulan',
        'year' => 'Tahun',
        'reset_action_heading' => 'Yakin reset item?',
        'reset_action_description' => 'Semua item yang ada akan direset',
        'set' => 'Tandai sebagai',
    ],
];
