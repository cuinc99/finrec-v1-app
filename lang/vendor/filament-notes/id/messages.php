<?php

return [
    "title" => "Catatan",
    "single" => "Catatan",
    "group" => "Konten",
    "pages" => [
        "groups" => "Kelola Grup Catatan",
        "status" => "Kelola Status Catatan"
    ],
    "columns" => [
        "title" => "Judul",
        "body" => "Isi",
        "date" => "Tanggal",
        "time" => "Waktu",
        "is_pined" => "Sematkan ke Dasbor",
        "is_public" => "Tampilkan untuk Publik",
        "icon" => "Ikon",
        "background" => "Latar Belakang",
        "border" => "Batas",
        "color" => "Warna",
        "font_size" => "Ukuran Font",
        "font" => "Font",
        "group" => "Grup",
        "status" => "Status",
        "user_id" => "ID Pengguna",
        "user_type" => "Tipe Pengguna",
        "model_id" => "ID Model",
        "model_type" => "Tipe Model",
        "created_at" => "Dibuat Pada",
        "updated_at" => "Diperbarui Pada"
    ],
    "tabs" => [
        "general" => "Umum",
        "style" => "Style"
    ],
    "actions" => [
        "view" => "Lihat",
        "edit" => "Edit",
        "delete" => "Hapus",
        "notify" => [
            "label" => "Beritahu Pengguna",
            "notification" => [
                "title" => "Notifikasi Terkirim",
                "body" => "Notifikasi telah terkirim."
            ]
        ],
        "share" => [
            "label" => "Bagikan Catatan",
            "notification" => [
                "title" => "Tautan Bagikan Catatan Dibuat",
                "body" => "Tautan bagikan catatan telah dibuat dan disalin ke clipboard."
            ]
        ],
        "user_access" => [
            "label" => "Bagikan ke Pengguna",
            "form" => [
                "model_id" => "Pengguna",
                "model_type" => "Tipe Pengguna",
            ],
            "notification" => [
                "title" => "Catatan Dibagikan",
                "body" => "Catatan berhasil dibagikan."
            ]
        ],
        "checklist"=> [
            "label" => "Tambah Daftar Checklist",
            "form" => [
                "checklist"=> "Daftar Checklist"
            ],
            "state" => [
                "done" => "Selesai",
                "pending" => "Tertunda"
            ],
            "notification" => [
                "title" => "Daftar Checklist Diperbarui",
                "body" => "Daftar checklist telah diperbarui.",
                "updated" => [
                    "title" => "Item Daftar Checklist Diperbarui",
                    "body" => "Item daftar checklist telah diperbarui."
                ],
            ]
        ]
    ],
    "notifications" => [
        "edit" => [
            "title" => "Catatan Diperbarui",
            "body" => "Catatan telah diperbarui."
        ],
        "delete" => [
            "title" => "Catatan Dihapus",
            "body" => "Catatan telah dihapus."
        ]
    ]
];
