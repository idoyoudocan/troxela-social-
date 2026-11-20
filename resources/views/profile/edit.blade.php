@extends('layouts.app')
@section('title', 'Edit profile')
@section('content')
<div class="card" style="max-width:560px;margin:0 auto">
  <h1>Edit profile</h1>
  <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <label for="name">Full name</label>
    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="100">
    @error('name')<div class="error">{{ $message }}</div>@enderror
    <label for="username">Username</label>
    <input id="username" type="text" name="username" value="{{ old('username', $user->username) }}" required maxlength="30">
    @error('username')<div class="error">{{ $message }}</div>@enderror
    <label for="bio">Bio</label>
    <textarea id="bio" name="bio" rows="3" maxlength="500">{{ old('bio', $user->bio) }}</textarea>
    <label for="date_of_birth">Date of birth</label>
    <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
    <label for="avatar">Avatar (optional)</label>
    @if($user->avatarUrl())<img class="avatar" src="{{ $user->avatarUrl() }}" alt="">@endif
    <input id="avatar" type="file" name="avatar" accept="image/*">
    @error('avatar')<div class="error">{{ $message }}</div>@enderror
    <div style="margin-top:14px"><button type="submit">Save profile</button></div>
  </form>
</div>
@endsection
