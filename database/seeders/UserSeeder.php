<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Administrator')->first();
        $supervisorRole = Role::where('name', 'Supervisor')->first();
        $employeeRole = Role::where('name', 'Employee')->first();

        $itDivision = Division::where('name', 'Information Technology')->first();
        $itManagerPosition = Position::where('name', 'IT Manager')->first();
        $itSupportPosition = Position::where('name', 'IT Support')->first();

        // 1. Superadmin User
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'Administrator Sistem',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole?->id,
                'status' => 'Active',
            ]
        );

        // 2. Supervisor User (Budi - IT Manager)
        $budi = User::updateOrCreate(
            ['username' => 'budi'],
            [
                'full_name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'division_id' => $itDivision?->id,
                'position_id' => $itManagerPosition?->id,
                'role_id' => $supervisorRole?->id,
                'status' => 'Active',
            ]
        );

        // Set Budi as Head of IT Division
        if ($itDivision && $budi) {
            $itDivision->update(['head_user_id' => $budi->id]);
        }

        // 3. Employee User (Ahmad - IT Support, supervised by Budi)
        User::updateOrCreate(
            ['username' => 'ahmad'],
            [
                'full_name' => 'Ahmad Fauzi',
                'password' => Hash::make('password123'),
                'division_id' => $itDivision?->id,
                'position_id' => $itSupportPosition?->id,
                'supervisor_id' => $budi?->id,
                'role_id' => $employeeRole?->id,
                'status' => 'Active',
            ]
        );
    }
}
