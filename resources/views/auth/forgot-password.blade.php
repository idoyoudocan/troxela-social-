@extends('layouts.app')
@section('title', 'Forgot password')
@section('content')
<div class="card" style="max-width:480px;margin:0 auto">
  <h1>Forgot password?</h1>
  <p class="muted">No problem. Enter the email you signed up with and we will send you a link to reset your password.</p>
  <form method="POST" action="{{ route('password.email') }}">
    @csrf
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
    @error('email')<div class="error">{{ $message }}</div>@enderror
    <div style="margin-top:14px"><button type="submit">Email password reset link</button></div>
  </form>
  <p class="small muted">Remembered it? <a href="{{ route('login') }}">Back to log in</a></p>
</div>
@endsection
