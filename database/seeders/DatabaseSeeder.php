<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Data sirf: admin ka email + password (.env se).
     * Baaki poori database khaali.
     */
    public function run(): void
    {
        $this->call(AdminSeeder::class);
    }
}
