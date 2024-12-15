<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Enums\CustomerTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Budi Santoso',
                'type' => CustomerTypeEnum::RESELLER->value,
                'user_id' => 2
            ],
            [
                'name' => 'Siti Rahma',
                'type' => CustomerTypeEnum::RESELLER->value,
                'user_id' => 2
            ],
            [
                'name' => 'Ahmad Wijaya',
                'type' => CustomerTypeEnum::PEMBELI->value,
                'user_id' => 2
            ],
            [
                'name' => 'Dewi Kartika',
                'type' => CustomerTypeEnum::RESELLER->value,
                'user_id' => 2
            ],
            [
                'name' => 'Rudi Hartono',
                'type' => CustomerTypeEnum::PEMBELI->value,
                'user_id' => 2
            ]
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
