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
        // 1. Membuat data Company bawaan (Master PT)
        $company = Company::firstOrCreate(['name' => 'PT. Drone Inovasi Master']);
        
        // FIX UTAMA: Menghapus 'company_id' karena model & tabel Department sudah resmi berdiri sendiri secara universal
        $dept = Department::firstOrCreate(['name' => 'Surveyor Core']);

        // 2. Membuat atau memperbarui data Akun Super Admin
        User::updateOrCreate(
            ['email' => 'admin@drone.com'],
            [
                'name' => 'Super Admin Drone',
                'full_name' => 'Super Admin Drone',
                'employee_id' => 'ADMIN001',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'company_id' => $company->id, // ◄--- KUNCI SAAS: User dihubungkan langsung ke Company
                'department_id' => $dept->id,  // User tetap terhubung ke Department
                'is_approved' => true
            ]
        );
    }
}