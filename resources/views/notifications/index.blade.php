@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h1 style="margin:0">Notifications</h1>
    <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn-secondary btn-sm" type="submit">Mark all as read</button></form>
  </div>
  @forelse($notifications as $n)
  <div class="notif {{ $n->read_at ? '' : 'unread' }}">
    <div>{{ $n->data['message'] ?? '' }}<br><span class="muted small">{{ $n->created_at->diffForHumans() }}</span></div>
    <form method="POST" action="{{ route('notifications.read', $n->id) }}">@csrf<button class="btn-sm" type="submit">Open</button></form>
  </div>
  @empty
  <p class="muted">No notifications yet.</p>
  @endforelse
  {{ $notifications->links() }}
</div>
@endsection
