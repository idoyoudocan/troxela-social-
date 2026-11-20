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
            $posts = Post::with(['user', 'likes'])
                ->where('body', 'like', "%{$q}%")->latest()->paginate(10);
        }
        return view('search', compact('q', 'users', 'posts'));
    }
}
