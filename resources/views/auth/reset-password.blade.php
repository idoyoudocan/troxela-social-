@extends('layouts.app')
@section('title', 'Reset password')
@section('content')
<div class="card" style="max-width:480px;margin:0 auto">
  <h1>Reset password</h1>
  <p class="muted">Choose a new password for your account.</p>
  <form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus>
    @error('email')<div class="error">{{ $message }}</div>@enderror
    <label for="password">New password</label>
    <input id="password" type="password" name="password" required>
    @error('password')<div class="error">{{ $message }}</div>@enderror
    <label for="password_confirmation">Confirm new password</label>
    <input id="password_confirmation" type="password" name="password_confirmation" required>
    <div style="margin-top:14px"><button type="submit">Reset password</button></div>
  </form>
</div>
@endsection
