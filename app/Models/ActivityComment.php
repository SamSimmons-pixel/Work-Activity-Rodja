<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'user_id',
        'comment',
    ];

    /**
     * The activity that this comment belongs to.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * The author of the comment (Supervisor/Admin).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
