<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function show(Post $post): View
    {
        $post->load(['user', 'likes', 'comments.user']);
        return view('posts.show', compact('post'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);
        $post = new Post(['body' => $data['body'], 'user_id' => Auth::id()]);
        if ($request->hasFile('image')) {
            $post->image = $request->file('image')->store('posts', 'public');
        }
        $post->save();
        return back()->with('status', 'Post published.');
    }

    public function edit(Post $post): View
    {
        abort_unless($post->user_id === Auth::id(), 403);
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === Auth::id(), 403);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);
        $post->body = $data['body'];
        if ($request->hasFile('image')) {
            if ($post->image) Storage::disk('public')->delete($post->image);
            $post->image = $request->file('image')->store('posts', 'public');
        }
        $post->save();
        return redirect()->route('posts.show', $post)->with('status', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        abort_unless($post->user_id === Auth::id(), 403);
        if ($post->image) Storage::disk('public')->delete($post->image);
        $post->delete();
        return redirect()->route('home')->with('status', 'Post deleted.');
    }

    public function toggleLike(Post $post): RedirectResponse
    {
        $user = Auth::user();
        $like = $post->likes()->where('user_id', $user->id)->first();
        if ($like) {
            $like->delete();
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            if ($post->user_id !== $user->id) {
                $post->user->notify(new UserNotification(
                    $user->name . ' liked your post.', route('posts.show', $post), 'like'));
            }
        }
        return back();
    }

    public function storeComment(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:1000']]);
        $post->comments()->create(['user_id' => Auth::id(), 'body' => $data['body']]);
        $user = Auth::user();
        if ($post->user_id !== $user->id) {
            $post->user->notify(new UserNotification(
                $user->name . ' commented on your post.', route('posts.show', $post), 'comment'));
        }
        return back()->with('status', 'Comment added.');
    }

    public function destroyComment(Comment $comment): RedirectResponse
    {
        abort_unless($comment->user_id === Auth::id() || $comment->post->user_id === Auth::id(), 403);
        $comment->delete();
        return back()->with('status', 'Comment deleted.');
    }
}
