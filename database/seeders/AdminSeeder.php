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
        $email    = (string) config('services.portal.admin.email', 'admin@studenthub.com');
        $password = (string) config('services.portal.admin.password', 'admin123');
        $name     = (string) config('services.portal.admin.name', 'Admin');

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
