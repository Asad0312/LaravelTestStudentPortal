<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Sirf admin ka account banata hai (.env se ADMIN_EMAIL / ADMIN_PASSWORD).
     * Baaki data bilkul khaali — students/tests etc. admin panel se add honge.
     */
    public function run(): void
    {
        $email    = (string) env('ADMIN_EMAIL', 'admin@studenthub.com');
        $password = (string) env('ADMIN_PASSWORD', 'admin123');
        $name     = (string) env('ADMIN_NAME', 'Admin');

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
                'role'     => 'Headmaster',
            ]
        );
    }
}
