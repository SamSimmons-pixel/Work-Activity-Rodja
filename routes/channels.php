<?php

use App\Models\Activity;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

/**
 * Private user notification channel.
 * Used by Laravel's built-in BroadcastNotificationCreated event.
 */
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Private activity comment channel.
 * Scoped per activity — only the activity owner and their supervisor may listen.
 * Used by ActivityCommentPosted event for real-time comment updates.
 */
Broadcast::channel('activity.{activityId}', function ($user, $activityId) {
    $activity = Activity::find($activityId);

    if (! $activity) {
        return false;
    }

    // Activity owner
    if ((int) $user->id === (int) $activity->user_id) {
        return true;
    }

    // Direct supervisor of the activity owner
    $activityOwner = $activity->user;
    if ($activityOwner && (int) $activityOwner->supervisor_id === (int) $user->id) {
        return true;
    }

    // Administrator can access any activity channel
    if ($user->hasRole('Administrator')) {
        return true;
    }

    return false;
});
