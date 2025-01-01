<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

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
                'selling_price' => 15000000,
                'user_id' => 2,
            ],
            [
                'name' => 'Smartphone Samsung Galaxy',
                'description' => 'Ponsel pintar dengan kamera resolusi tinggi',
                'selling_price' => 6500000,
                'user_id' => 2,
            ],
            [
                'name' => 'Printer Epson L3210',
                'description' => 'Printer inkjet multifungsi untuk kebutuhan kantor',
                'selling_price' => 3000000,
                'user_id' => 2,
            ],
            [
                'name' => 'Monitor LG UltraWide',
                'description' => 'Monitor lebar dengan kualitas tampilan premium',
                'selling_price' => 5000000,
                'user_id' => 2,
            ],
            [
                'name' => 'Keyboard Mechanical Logitech',
                'description' => 'Keyboard gaming dengan switch berkualitas tinggi',
                'selling_price' => 2000000,
                'user_id' => 2,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
