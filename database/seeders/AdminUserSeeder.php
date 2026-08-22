<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\employee;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::firstOrCreate(
            [
                'email' => 'admin@gmail.com'
            ],
            [
                'first_name' => 'System',
                'last_name' => 'Admin',
                'employee_id' => 'ADM001',
                'ic_number' => '000000000000',
                'phone_number' => '0123456789',
                'password' => Hash::make('admin123'),
                'status' => 'Active',
            ]
        );

        employee::updateOrCreate(
            [
                'user_id' => $admin->id,
            ],
            [
                'role' => 'Admin',
            ]
        );
    }
}
