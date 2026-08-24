<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'head_user_id',
        'status',
    ];

    /**
     * The division head user.
     */
    public function headUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    /**
     * Positions within this division.
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Users belonging to this division.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
