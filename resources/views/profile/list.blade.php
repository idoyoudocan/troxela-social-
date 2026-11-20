@extends('layouts.app')
@section('title', $title . ' — ' . $user->name)
@section('content')
<div class="card">
  <h1>{{ $title }} — {{ $user->name }}</h1>
  @forelse($users as $u)
  <div class="user-row">
    <img class="avatar" src="{{ $u->avatarUrl() }}" alt="">
    <div style="flex:1"><a href="{{ route('profile.show', $u) }}"><b>{{ $u->name }}</b></a><br><span class="muted small">{{ '@' . $u->username }}</span></div>
    @auth
      @if(auth()->id() !== $u->id)
      <form method="POST" action="{{ route('users.follow', $u) }}">@csrf<button class="btn-sm" type="submit">{{ auth()->user()->isFollowing($u) ? 'Unfollow' : 'Follow' }}</button></form>
      @endif
    @endauth
  </div>
  @empty
  <p class="muted">Nobody here yet.</p>
  @endforelse
  {{ $users->links() }}
</div>
@endsection
