<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Membuat data Company bawaan (Master PT) - Menggunakan nama sebagai pencari unik
        $company = Company::firstOrCreate(
            ['name' => 'PT. Droneasia']
        );
        
        // 2. Membuat data Department bawaan - Menggunakan nama sebagai pencari unik
        $dept = Department::firstOrCreate(
            ['name' => 'Surveyor']
        );

        // 3. Membuat atau memperbarui data Akun Super Admin
        // Menggunakan email sebagai patokan unik agar tidak terjadi duplikasi akun
        User::updateOrCreate(
            ['email' => 'admin@drone.com'], // Patokan unik pencarian
            [
                'name' => 'Super Admin Drone',
                'full_name' => 'Super Admin Drone',
                'employee_id' => 'ADMIN001',
                'password' => Hash::make('password123'), // ◄--- SUDAH AMAN (Bcrypt otomatis)
                'role' => 'super_admin',
                'company_id' => $company->id,
                'department_id' => $dept->id,
                'is_approved' => true
            ]
        );
    }
}