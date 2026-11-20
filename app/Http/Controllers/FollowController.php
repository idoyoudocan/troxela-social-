<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(User $user): RedirectResponse
    {
        $me = Auth::user();
        abort_if($me->id === $user->id, 403, 'You cannot follow yourself.');
        if ($me->isFollowing($user)) {
            $me->following()->detach($user->id);
        } else {
            $me->following()->attach($user->id);
            $user->notify(new UserNotification(
                $me->name . ' started following you.', route('profile.show', $me), 'follow'));
        }
        return back();
    }
}
