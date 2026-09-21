<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Production-safe seed: the admin login and the real service list.
     * Sample jobs / photos / enquiries live in DemoContentSeeder and are never run from here.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ServiceSeeder::class,
        ]);
    }
}
