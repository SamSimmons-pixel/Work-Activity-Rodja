<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'description' => 'Akses penuh ke manajemen user, role, divisi, posisi, dan seluruh aktivitas organisasi.',
            ],
            [
                'name' => 'Supervisor',
                'description' => 'Mencatat aktivitas mandiri dan memantau aktivitas bawahan langsung.',
            ],
            [
                'name' => 'Employee',
                'description' => 'Mencatat, mengedit, dan memantau riwayat aktivitas pekerjaan pribadi.',
            ],
            [
                'name' => 'Management',
                'description' => 'Melihat laporan dan aktivitas pekerjaan di seluruh divisi atau organisasi.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
