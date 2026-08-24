<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasPermissions;

    protected $fillable = [
        'username',
        'full_name',
        'password',
        'position_id',
        'division_id',
        'supervisor_id',
        'role_id',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * User's assigned role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * User's division.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * User's position.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * User's direct supervisor.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * User's direct subordinates.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Activities recorded by this user.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Performance reviews received by this user.
     */
    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class, 'user_id')->orderBy('end_date', 'desc');
    }

    /**
     * Performance reviews written by this user (as supervisor/reviewer).
     */
    public function givenPerformanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class, 'reviewer_id')->orderBy('end_date', 'desc');
    }

    /**
     * Check if account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    /**
     * Get IDs of direct subordinates.
     */
    public function getSubordinateIds(): array
    {
        return $this->subordinates()->pluck('id')->toArray();
    }
}
