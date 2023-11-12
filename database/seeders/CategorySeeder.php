<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("categories")->insert([
            'id' => 'GLASSES',
            'name' => 'Glasses',
            'created_at' => '2023-10-10 10:10:10'
        ]);
        DB::table("categories")->insert([
            'id' => 'CAR',
            'name' => 'Car',
            'created_at' => '2023-10-10 10:10:10'
        ]);
        DB::table("categories")->insert([
            'id' => 'OAK',
            'name' => 'Oak',
            'created_at' => '2023-10-10 10:10:10'
        ]);
        DB::table("categories")->insert([
            'id' => 'BOOK',
            'name' => 'Book',
            'created_at' => '2023-10-10 10:10:10'
        ]);
    }
}
