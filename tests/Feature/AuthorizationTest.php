<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_employee_can_only_view_own_activity(): void
    {
        $ahmad = User::where('username', 'ahmad')->first(); // Employee

        $employeeRole = Role::where('name', 'Employee')->first();
        $otherEmployee = User::create([
            'username' => 'siti',
            'full_name' => 'Siti Nurhaliza',
            'password' => 'password123',
            'role_id' => $employeeRole->id,
            'status' => 'Active',
        ]);

        $ahmadActivity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => '2026-08-24',
            'activity' => 'Mengerjakan perbaikan server',
            'requested_by' => 'IT Manager',
            'result' => 'Server kembali normal',
            'status' => 'Submitted',
        ]);

        $sitiActivity = Activity::create([
            'user_id' => $otherEmployee->id,
            'activity_date' => '2026-08-24',
            'activity' => 'Update dokumen keuangan',
            'requested_by' => 'Finance Head',
            'result' => 'Dokumen selesai',
            'status' => 'Submitted',
        ]);

        // Ahmad can view own activity
        $this->assertTrue(Gate::forUser($ahmad)->allows('view-activity', $ahmadActivity));

        // Ahmad CANNOT view Siti's activity (IDOR check)
        $this->assertFalse(Gate::forUser($ahmad)->allows('view-activity', $sitiActivity));
    }

    public function test_supervisor_can_view_subordinates_activity_but_not_unrelated_employees(): void
    {
        $budi = User::where('username', 'budi')->first(); // Supervisor over Ahmad
        $ahmad = User::where('username', 'ahmad')->first(); // Ahmad is supervised by Budi

        $employeeRole = Role::where('name', 'Employee')->first();
        $dian = User::create([
            'username' => 'dian',
            'full_name' => 'Dian Kusuma',
            'password' => 'password123',
            'role_id' => $employeeRole->id,
            'status' => 'Active',
        ]);

        $ahmadActivity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => '2026-08-24',
            'activity' => 'Setup SSL Certificate',
            'requested_by' => 'Self Initiative',
            'result' => 'SSL aktif',
            'status' => 'Submitted',
        ]);

        $dianActivity = Activity::create([
            'user_id' => $dian->id,
            'activity_date' => '2026-08-24',
            'activity' => 'Rekap data operasional',
            'requested_by' => 'Operational Manager',
            'result' => 'Rekap selesai',
            'status' => 'Submitted',
        ]);

        // Supervisor can view subordinate's activity
        $this->assertTrue(Gate::forUser($budi)->allows('view-activity', $ahmadActivity));

        // Supervisor CANNOT view unrelated employee's activity
        $this->assertFalse(Gate::forUser($budi)->allows('view-activity', $dianActivity));
    }

    public function test_administrator_has_full_management_and_activity_access(): void
    {
        $admin = User::where('username', 'admin')->first();
        $ahmad = User::where('username', 'ahmad')->first();

        $ahmadActivity = Activity::create([
            'user_id' => $ahmad->id,
            'activity_date' => '2026-08-24',
            'activity' => 'Migrasi database',
            'requested_by' => 'IT Manager',
            'result' => 'Migrasi sukses',
            'status' => 'Submitted',
        ]);

        $this->assertTrue(Gate::forUser($admin)->allows('view-activity', $ahmadActivity));
        $this->assertTrue(Gate::forUser($admin)->allows('user.manage'));
        $this->assertTrue(Gate::forUser($admin)->allows('role.manage'));
        $this->assertTrue(Gate::forUser($admin)->allows('division.manage'));
    }
}
