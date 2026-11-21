@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<div class="card" style="max-width:480px;margin:0 auto">
  <h1>Welcome back</h1>
  <p class="muted">Enter a sanctuary of quiet, premium thoughts.</p>
  <form method="POST" action="{{ route('login') }}">
    @csrf
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
    @error('email')<div class="error">{{ $message }}</div>@enderror
    <label for="password">Password</label>
    <input id="password" type="password" name="password" required>
    @error('password')<div class="error">{{ $message }}</div>@enderror
    <label style="display:flex;gap:6px;align-items:center"><input type="checkbox" name="remember" style="width:auto"> Remember me</label>
    <div style="margin-top:14px"><button type="submit">Log in</button></div>
  </form>
  <p class="small"><a href="{{ route('password.request') }}">Forgot your password?</a></p>
  <p class="small muted">Don't have an account? <a href="{{ route('register') }}">Create one</a></p>
</div>
@endsection
