<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Activity permissions
            ['name' => 'activity.create', 'description' => 'Membuat catatan aktivitas baru'],
            ['name' => 'activity.read.own', 'description' => 'Melihat aktivitas milik sendiri'],
            ['name' => 'activity.update.own', 'description' => 'Mengedit aktivitas milik sendiri'],
            ['name' => 'activity.delete.own', 'description' => 'Menghapus aktivitas milik sendiri'],
            ['name' => 'activity.read.subordinate', 'description' => 'Melihat aktivitas bawahan langsung'],
            ['name' => 'activity.read.division', 'description' => 'Melihat aktivitas dalam satu divisi'],
            ['name' => 'activity.read.all', 'description' => 'Melihat seluruh aktivitas organisasi'],

            // Admin management permissions
            ['name' => 'user.manage', 'description' => 'Mengelola data pengguna dan aktivasi akun'],
            ['name' => 'role.manage', 'description' => 'Mengelola peran dan izin akses'],
            ['name' => 'division.manage', 'description' => 'Mengelola struktur divisi'],
            ['name' => 'position.manage', 'description' => 'Mengelola jabatan / posisi'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }
    }
}
