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
            'purchase_price' => 'Harga Beli',
            'selling_price' => 'Harga Jual',
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
            'discount' => 'Diskon (Rp)',
            'subtotal' => 'Subtotal',
            'subtotal_after_discount' => 'Subtotal Setelah Diskon',
            'capital_per_item' => 'Modal per Item',
            'capital' => 'Modal',
            'profit_per_item' => 'Profit per Item',
            'profit' => 'Profit',
            'is_paid' => 'Status',
            'is_paid_question' => 'Pembayaran Lunas?',
            'product' => 'Produk',
            'customer' => 'Pelanggan',
            'is_paid_options' => [
                'paid' => 'Lunas',
                'unpaid' => 'Belum Lunas',
            ]
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
    ],
];
