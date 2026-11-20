<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        $posts = $user->posts()->with(['likes', 'comments.user'])->latest()->paginate(10);
        return view('profile.show', compact('user', 'posts'));
    }

    public function edit(): View
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);
        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($data);
        return redirect()->route('profile.show', $user->fresh())->with('status', 'Profile updated.');
    }

    public function followers(User $user): View
    {
        $users = $user->followers()->paginate(20);
        return view('profile.list', ['user' => $user, 'users' => $users, 'title' => 'Followers']);
    }

    public function following(User $user): View
    {
        $users = $user->following()->paginate(20);
        return view('profile.list', ['user' => $user, 'users' => $users, 'title' => 'Following']);
    }
}
