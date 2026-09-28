<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * All model-specific seeders are separated cleanly and run idempotently.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            AgentSeeder::class,
            PropertySeeder::class,
            SettingSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
