<?php

namespace App\Livewire\Organization;

use App\Models\Division;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Bagan Organisasi')]
class Chart extends Component
{
    public function render()
    {
        // Get all active users with their hierarchy relations
        $users = User::with(['position', 'division', 'role', 'subordinates.position', 'subordinates.division'])
            ->where('status', 'Active')
            ->orderBy('full_name')
            ->get();

        // Find top level roots (users with no supervisor or whose supervisor is inactive)
        $rootUsers = $users->filter(function ($user) {
            return is_null($user->supervisor_id);
        });

        // If no root has supervisor_id null, fallback to users with level 1 positions or managers
        if ($rootUsers->isEmpty()) {
            $rootUsers = $users->filter(function ($user) {
                return $user->position?->level === 1 || $user->role?->name === 'Administrator';
            });
        }

        $divisions = Division::with(['headUser', 'positions', 'users'])->where('status', 'Active')->get();

        return view('livewire.organization.chart', [
            'rootUsers' => $rootUsers,
            'allUsers' => $users,
            'divisions' => $divisions,
        ]);
    }
}
