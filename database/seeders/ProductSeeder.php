<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop Asus ROG',
                'description' => 'Laptop gaming berkinerja tinggi dengan prosesor terkini',
                'purchase_price' => 12000000.00,
                'selling_price' => 15000000.00,
                'user_id' => 2
            ],
            [
                'name' => 'Smartphone Samsung Galaxy',
                'description' => 'Ponsel pintar dengan kamera resolusi tinggi',
                'purchase_price' => 5000000.00,
                'selling_price' => 6500000.00,
                'user_id' => 2
            ],
            [
                'name' => 'Printer Epson L3210',
                'description' => 'Printer inkjet multifungsi untuk kebutuhan kantor',
                'purchase_price' => 2500000.00,
                'selling_price' => 3000000.00,
                'user_id' => 2
            ],
            [
                'name' => 'Monitor LG UltraWide',
                'description' => 'Monitor lebar dengan kualitas tampilan premium',
                'purchase_price' => 4000000.00,
                'selling_price' => 5000000.00,
                'user_id' => 2
            ],
            [
                'name' => 'Keyboard Mechanical Logitech',
                'description' => 'Keyboard gaming dengan switch berkualitas tinggi',
                'purchase_price' => 1500000.00,
                'selling_price' => 2000000.00,
                'user_id' => 2
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
