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
            'qty' => 'Jumlah',
            'discount_price' => 'Harga Diskon',
            'profit_price' => 'Harga Profit',
            'subtotal' => 'Subtotal',
            'subtotal_after_discount' => 'Subtotal Setelah Diskon',
            'pay' => 'Bayar',
            'is_paid' => 'Sudah Dibayar',
            'product' => 'Produk',
            'customer' => 'Pelanggan',
        ],
    ],
    'common' => [
        'created_at' => 'Dibuat Pada',
        'updated_at' => 'Diperbarui Pada',
        'deleted_at' => 'Dihapus Pada',
        'actions' => 'Aksi',
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
    ],
];
