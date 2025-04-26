<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name' => 'Beauty Care',
            ],
            [
                'name' => 'Sports Nutrition',
            ],
            [
                'name' => 'Personal Care',
            ],
            [
                'name' => 'Home Healthcare',
            ],
            [
                'name' => 'Mother&Baby Care',
            ]
        ]);
    }
}
