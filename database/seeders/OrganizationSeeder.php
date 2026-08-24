<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Position;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            'Information Technology' => [
                ['name' => 'IT Manager', 'level' => '1'],
                ['name' => 'IT Supervisor', 'level' => '2'],
                ['name' => 'System Administrator', 'level' => '3'],
                ['name' => 'Developer', 'level' => '3'],
                ['name' => 'IT Support', 'level' => '3'],
                ['name' => 'IT Staff', 'level' => '4'],
            ],
            'Finance' => [
                ['name' => 'Finance Manager', 'level' => '1'],
                ['name' => 'Accounting Staff', 'level' => '3'],
                ['name' => 'Finance Staff', 'level' => '3'],
            ],
            'Human Resources' => [
                ['name' => 'HR Manager', 'level' => '1'],
                ['name' => 'HR Staff', 'level' => '3'],
            ],
            'Administration' => [
                ['name' => 'Admin Manager', 'level' => '1'],
                ['name' => 'General Affair', 'level' => '3'],
            ],
            'Operational' => [
                ['name' => 'Operation Manager', 'level' => '1'],
                ['name' => 'Operation Staff', 'level' => '3'],
            ],
        ];

        foreach ($divisions as $divisionName => $positions) {
            $division = Division::firstOrCreate(
                ['name' => $divisionName],
                ['status' => 'Active']
            );

            foreach ($positions as $pos) {
                Position::firstOrCreate(
                    [
                        'division_id' => $division->id,
                        'name' => $pos['name'],
                    ],
                    [
                        'level' => $pos['level'],
                        'status' => 'Active',
                    ]
                );
            }
        }
    }
}
