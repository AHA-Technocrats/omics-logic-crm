<?php

namespace Database\Seeders;

use AHATechnocrats\Installer\Database\Seeders\DatabaseSeeder as KrayinDatabaseSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(KrayinDatabaseSeeder::class);
    }
}
