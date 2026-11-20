@extends('layouts.app')
@section('title', 'Home')
@section('content')
<div class="grid2">
  <div>
    @auth
    <div class="card">
      <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
        @csrf
        <textarea name="body" rows="3" placeholder="What's on your mind, {{ auth()->user()->name }}?" required maxlength="2000">{{ old('body') }}</textarea>
        @error('body')<div class="error">{{ $message }}</div>@enderror
        <div style="display:flex;gap:10px;align-items:center;margin-top:10px">
          <input type="file" name="image" accept="image/*" style="flex:1">
          <button type="submit">Post</button>
        </div>
        @error('image')<div class="error">{{ $message }}</div>@enderror
      </form>
    </div>
    @else
    <div class="card"><h2>Welcome to Troxela</h2><p class="muted">A quiet, premium social space. <a href="{{ route('register') }}">Create an account</a> or <a href="{{ route('login') }}">log in</a> to post, follow people and message friends.</p></div>
    @endauth

    @forelse($posts as $post)
      @include('partials.post', ['post' => $post])
    @empty
      <div class="card"><p class="muted">No posts yet. Follow some people or be the first to post!</p></div>
    @endforelse
    {{ $posts->links() }}
  </div>
  <aside>
    @auth
    <div class="card">
      <h3>Your profile</h3>
      <div class="user-row">
        <img class="avatar" src="{{ auth()->user()->avatarUrl() }}" alt="">
        <div><b>{{ auth()->user()->name }}</b><br><span class="muted small">{{ '@' . auth()->user()->username }}</span></div>
      </div>
      <p class="small muted">{{ auth()->user()->followers()->count() }} followers · {{ auth()->user()->following()->count() }} following</p>
      <a class="btn btn-secondary btn-sm" href="{{ route('profile.edit') }}">Edit profile</a>
    </div>
    @if(!empty($suggestions) && $suggestions->count())
    <div class="card"><h3>People to follow</h3>
      @foreach($suggestions as $s)
      <div class="user-row">
        <img class="avatar" src="{{ $s->avatarUrl() }}" alt="" style="width:36px;height:36px">
        <div style="flex:1"><a href="{{ route('profile.show', $s) }}"><b>{{ $s->name }}</b></a><br><span class="muted small">{{ '@' . $s->username }}</span></div>
        <form method="POST" action="{{ route('users.follow', $s) }}">@csrf<button class="btn-sm" type="submit">Follow</button></form>
      </div>
      @endforeach
    </div>
    @endif
    @endauth
    <div class="card"><h3>About</h3><p class="small muted">Troxela is a social space for quiet, premium thoughts. Share posts, follow friends, comment, like and message — all in one place.</p></div>
  </aside>
</div>
@endsection
