<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'activity_date',
        'activity',
        'requested_by',
        'category',
        'tags',
        'result',
        'constraint',
        'status',
        'attachment_path',
        'attachment_name',
        'verified_at',
        'verified_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'verified_at' => 'datetime',
            'tags' => 'array',
        ];
    }

    /**
     * The owner of the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User who created the activity record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who last updated the activity record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * User who deleted the activity record.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Supervisor/Admin who verified the activity.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Comments and feedback on this activity.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ActivityComment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Helper to check if file is an image.
     */
    public function hasImageAttachment(): bool
    {
        if (empty($this->attachment_path)) {
            return false;
        }

        $extension = strtolower(pathinfo($this->attachment_path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    /**
     * Scope to filter activities by specific month and year.
     */
    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('activity_date', $year)
                     ->whereMonth('activity_date', $month);
    }

    /**
     * Scope to filter activities by category.
     */
    public function scopeForCategory(Builder $query, ?string $category): Builder
    {
        if (empty($category) || $category === 'all') {
            return $query;
        }

        return $query->where('category', $category);
    }

    /**
     * Scope to search within activity, result, constraint, requested_by, category, and tags.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('activity', 'like', "%{$term}%")
              ->orWhere('result', 'like', "%{$term}%")
              ->orWhere('constraint', 'like', "%{$term}%")
              ->orWhere('requested_by', 'like', "%{$term}%")
              ->orWhere('category', 'like', "%{$term}%");
        });
    }
}
