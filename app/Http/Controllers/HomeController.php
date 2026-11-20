<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if (! $user) {
            $posts = Post::with(['user', 'likes', 'comments.user'])->latest()->paginate(10);
            return view('home', compact('posts'));
        }
        $ids = $user->following()->pluck('users.id')->push($user->id);
        $posts = Post::with(['user', 'likes', 'comments.user'])
            ->whereIn('user_id', $ids)->latest()->paginate(10);
        $suggestions = \App\Models\User::where('id', '!=', $user->id)
            ->whereNotIn('id', $user->following()->pluck('users.id'))
            ->inRandomOrder()->limit(5)->get();
        return view('home', compact('posts', 'suggestions'));
    }

    public function explore(): View
    {
        $posts = Post::with(['user', 'likes', 'comments.user'])->latest()->paginate(12);
        return view('explore', compact('posts'));
    }
}
