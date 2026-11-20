@extends('layouts.app')
@section('title', 'Messages')
@section('content')
<div class="card">
  <h1>Messages</h1>
  @forelse($threads as $t)
  <div class="user-row">
    <img class="avatar" src="{{ $t['user']->avatarUrl() }}" alt="">
    <div style="flex:1">
      <a href="{{ route('messages.show', $t['user']) }}"><b>{{ $t['user']->name }}</b></a>
      @if($t['unread'])<span class="badge">{{ $t['unread'] }}</span>@endif
      <br><span class="muted small">{{ $t['last'] ? \Illuminate\Support\Str::limit($t['last']->body, 80) : '' }}</span>
    </div>
    <span class="muted small">{{ $t['last']?->created_at->diffForHumans() }}</span>
  </div>
  @empty
  <p class="muted">No conversations yet. Visit someone's profile and click Message to start one.</p>
  @endforelse
</div>
@endsection
