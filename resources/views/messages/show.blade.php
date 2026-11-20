@extends('layouts.app')
@section('title', 'Chat with ' . $user->name)
@section('content')
<div class="card">
  <h1 style="display:flex;gap:10px;align-items:center"><img class="avatar" src="{{ $user->avatarUrl() }}" alt=""> {{ $user->name }}</h1>
  <div>
    @forelse($messages as $m)
      <div class="msg {{ $m->sender_id === auth()->id() ? 'me' : 'them' }}">
        {{ $m->body }}<br><span class="small" style="opacity:.7">{{ $m->created_at->diffForHumans() }}</span>
      </div>
    @empty
      <p class="muted">No messages yet — say hello!</p>
    @endforelse
    {{ $messages->links() }}
  </div>
  <form method="POST" action="{{ route('messages.store', $user) }}" style="margin-top:14px">
    @csrf
    <textarea name="body" rows="2" placeholder="Write a message..." required maxlength="2000"></textarea>
    <div style="margin-top:8px"><button type="submit">Send</button></div>
  </form>
</div>
@endsection
