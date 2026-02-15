<?php

namespace Database\Seeders;

use Database\Seeders\DemoSeeder as SeedersDemoSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SeedersDemoSeeder::class);
    }
}
