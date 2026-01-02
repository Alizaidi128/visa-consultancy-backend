<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class, // 👈 MUST be first
            CountrySeeder::class,
            VisaTypeSeeder::class,
            RequiredDocumentSeeder::class,
        ]);
    }
}
