@extends('layouts.app')
@section('title', 'Sign up')
@section('content')
<div class="card" style="max-width:520px;margin:0 auto">
  <h1>Create your account</h1>
  <p class="muted">Join Troxela — it takes less than a minute.</p>
  <form method="POST" action="{{ route('register') }}">
    @csrf
    <label for="name">Full name</label>
    <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="100">
    @error('name')<div class="error">{{ $message }}</div>@enderror
    <label for="username">Username</label>
    <input id="username" type="text" name="username" value="{{ old('username') }}" required maxlength="30" placeholder="e.g. idoyoudocan">
    @error('username')<div class="error">{{ $message }}</div>@enderror
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
    @error('email')<div class="error">{{ $message }}</div>@enderror
    <label for="date_of_birth">Date of birth</label>
    <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
    @error('date_of_birth')<div class="error">{{ $message }}</div>@enderror
    <label for="bio">Bio (optional)</label>
    <textarea id="bio" name="bio" rows="2" maxlength="500">{{ old('bio') }}</textarea>
    <label for="password">Password</label>
    <input id="password" type="password" name="password" required minlength="8">
    @error('password')<div class="error">{{ $message }}</div>@enderror
    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8">
    <div style="margin-top:14px"><button type="submit">Create account</button></div>
  </form>
  <p class="small muted">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
</div>
@endsection
