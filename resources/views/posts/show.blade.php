@extends('layouts.app')
@section('title', 'Post')
@section('content')
@include('partials.post', ['post' => $post])
<div class="card">
  <h3>Comments ({{ $post->comments->count() }})</h3>
  @auth
  <form method="POST" action="{{ route('posts.comments.store', $post) }}">
    @csrf
    <textarea name="body" rows="2" placeholder="Write a comment..." required maxlength="1000"></textarea>
    <div style="margin-top:8px"><button type="submit">Comment</button></div>
  </form>
  @else
  <p class="muted"><a href="{{ route('login') }}">Log in</a> to comment.</p>
  @endauth
  @foreach($post->comments as $comment)
  <div class="comment">
    <img class="avatar" src="{{ $comment->user->avatarUrl() }}" alt="" style="width:32px;height:32px">
    <div class="body">
      <a href="{{ route('profile.show', $comment->user) }}"><b>{{ $comment->user->name }}</b></a>
      <span class="muted small">{{ $comment->created_at->diffForHumans() }}</span><br>
      {{ $comment->body }}
      @auth
        @if($comment->user_id === auth()->id() || $post->user_id === auth()->id())
        <form method="POST" action="{{ route('comments.destroy', $comment) }}" style="display:inline">@csrf @method('DELETE')<button class="btn-sm btn-danger" type="submit">Delete</button></form>
        @endif
      @endauth
    </div>
  </div>
  @endforeach
</div>
@endsection
