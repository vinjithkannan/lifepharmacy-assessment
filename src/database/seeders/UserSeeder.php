<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'EcomAdmin',
                'email' => 'ecomadmin@lifepharmacy.com',
                'password' => Hash::make('ecomadmin'),
                'role' => 'admin'
            ],
            [
                'name' => 'Customer 1',
                'email' => 'customer1@lifepharmacy.com',
                'password' => Hash::make('customer1'),
                'role' => 'customer'
            ]
        ]);
    }
}
