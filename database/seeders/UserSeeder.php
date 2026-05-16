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
        $company = Company::firstOrCreate(['name' => 'PT. Drone Inovasi Master']);
        $dept = Department::firstOrCreate(['company_id' => $company->id, 'name' => 'Surveyor Core']);

        User::updateOrCreate(
            ['email' => 'admin@drone.com'],
            [
                'name' => 'Super Admin Drone',
                'full_name' => 'Super Admin Drone',
                'employee_id' => 'ADMIN001',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'company_id' => $company->id,
                'department_id' => $dept->id,
                'is_approved' => true
            ]
        );
    }
}