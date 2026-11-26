@extends('layouts.app')
@section('title', $user->name)
@section('content')
<div class="card">
  <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap">
    <img class="avatar-lg" src="{{ $user->avatarUrl() }}" alt="">
    <div style="flex:1;min-width:220px">
      <h1 style="margin:0">{{ $user->name }}</h1>
      <p class="muted">{{ '@' . $user->username }} · Joined {{ $user->created_at->format('M Y') }}</p>
      @if($user->bio)<p>{{ $user->bio }}</p>@endif
      <p>
        <a href="{{ route('profile.followers', $user) }}"><b>{{ $user->followers()->count() }}</b> followers</a> ·
        <a href="{{ route('profile.following', $user) }}"><b>{{ $user->following()->count() }}</b> following</a> ·
        <b>{{ $user->posts()->count() }}</b> posts
      </p>
    </div>
    @auth
      @if(auth()->id() === $user->id)
        <a class="btn btn-secondary" href="{{ route('profile.edit') }}">Edit profile</a>
      @else
        <form method="POST" action="{{ route('users.follow', $user) }}">@csrf
          <button type="submit">{{ auth()->user()->isFollowing($user) ? 'Unfollow' : 'Follow' }}</button>
        </form>
        <a class="btn btn-secondary chat-open" href="{{ route('messages.show', $user) }}" data-user-id="{{ $user->id }}" data-user-username="{{ $user->username }}" data-user-name="{{ $user->name }}" data-user-avatar="{{ $user->avatarUrl() }}">Message</a>
      @endif
    @endauth
  </div>
</div>
<h2>Posts</h2>
@forelse($posts as $post)
  @include('partials.post', ['post' => $post])
@empty
  <div class="card"><p class="muted">{{ $user->name }} hasn't posted anything yet.</p></div>
@endforelse
{{ $posts->links() }}
@endsection
