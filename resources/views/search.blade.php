@extends('layouts.app')
@section('title', 'Search')
@section('content')
<h1>Search</h1>
@if($q === '')
  <div class="card"><p class="muted">Type a name, username or word to search people and posts.</p></div>
@else
  <div class="card"><h2>People matching "{{ $q }}"</h2>
    @forelse($users as $u)
    <div class="user-row">
      <img class="avatar" src="{{ $u->avatarUrl() }}" alt="">
      <div style="flex:1"><a href="{{ route('profile.show', $u) }}"><b>{{ $u->name }}</b></a><br><span class="muted small">{{ '@' . $u->username }}</span></div>
    </div>
    @empty <p class="muted">No people found.</p> @endforelse
  </div>
  <h2>Posts matching "{{ $q }}"</h2>
  @forelse($posts as $post)
    @include('partials.post', ['post' => $post])
  @empty
    <div class="card"><p class="muted">No posts found.</p></div>
  @endforelse
  {{ $posts->links() }}
@endif
@endsection
