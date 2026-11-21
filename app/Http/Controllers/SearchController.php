<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $users = collect();
        $posts = collect();
        if ($q !== '') {
            $users = User::where('name', 'like', "%{$q}%")
                ->orWhere('username', 'like', "%{$q}%")->limit(20)->get();
            // Match post text AND the author's name/username, so searching
            // for a person also finds their posts.
            $posts = Post::with(['user', 'likes'])
                ->where(function ($query) use ($q) {
                    $query->where('body', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($userQuery) use ($q) {
                            $userQuery->where('name', 'like', "%{$q}%")
                                ->orWhere('username', 'like', "%{$q}%");
                        });
                })->latest()->paginate(10);
        }
        return view('search', compact('q', 'users', 'posts'));
    }
}
