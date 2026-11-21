@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<div class="card" style="max-width:560px;margin:0 auto">
  <h1>Settings</h1>

  <h2>Account</h2>
  <p><b>{{ $user->name }}</b><br>
  <span class="muted">{{ '@' . $user->username }} · {{ $user->email }}</span></p>
  <p><a class="btn btn-sm" href="{{ route('profile.edit') }}">Edit profile</a>
  <a class="btn-secondary btn-sm" href="{{ route('profile.show', $user) }}">View my profile</a></p>

  <h2>Password</h2>
  <p class="muted">To change your password, we will email you a reset link.</p>
  <p><a class="btn btn-sm" href="{{ route('password.request') }}">Send password reset link</a></p>

  <h2>Notifications</h2>
  <p class="muted">See your notifications, or mark them all as read.</p>
  <p><a class="btn btn-sm" href="{{ route('notifications.index') }}">View notifications</a></p>
  <form method="POST" action="{{ route('notifications.read-all') }}" style="margin-top:8px">@csrf<button class="btn-secondary btn-sm" type="submit">Mark all as read</button></form>
</div>
@endsection
