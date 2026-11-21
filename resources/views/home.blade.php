@extends('layouts.app')
@section('title', 'Home')
@section('mainclass', 'wide')
@section('content')
<div class="home-grid">
  {{-- ============ LEFT SIDEBAR ============ --}}
  <aside class="col-left">
    @auth
    <div class="card profile-card">
      <div class="profile-avatar-wrap">
        <img class="avatar-lg" src="{{ auth()->user()->avatarUrl() }}" alt="">
        <span class="profile-badge" title="Premium Creator">@include('partials.icon', ['name' => 'link', 'size' => 12])</span>
      </div>
      <h3>{{ auth()->user()->name }}</h3>
      <div class="handle">{{ '@' . auth()->user()->username }}</div>
      <div class="stats-row">
        <div class="stat"><b>{{ auth()->user()->posts()->count() }}</b><span>Posts</span></div>
        <div class="stat"><b>{{ auth()->user()->followers()->count() }}</b><span>Followers</span></div>
        <div class="stat"><b>{{ auth()->user()->following()->count() }}</b><span>Following</span></div>
      </div>
      <p class="premium-line"><span class="dot">●</span> Premium Creator Account</p>
    </div>
    @endauth

    <nav class="card nav-card" aria-label="Sidebar">
      <a class="nav-item active" href="{{ route('home') }}">@include('partials.icon', ['name' => 'home', 'size' => 20]) Home Feed</a>
      @auth
        <a class="nav-item" href="{{ route('profile.show', auth()->user()) }}">@include('partials.icon', ['name' => 'user', 'size' => 20]) My Profile</a>
        <a class="nav-item" href="{{ route('profile.following', auth()->user()) }}">@include('partials.icon', ['name' => 'users', 'size' => 20]) My Friends</a>
      @else
        <a class="nav-item" href="{{ route('login') }}">@include('partials.icon', ['name' => 'user', 'size' => 20]) My Profile</a>
        <a class="nav-item" href="{{ route('login') }}">@include('partials.icon', ['name' => 'users', 'size' => 20]) My Friends</a>
      @endauth
      <a class="nav-item" href="{{ route('explore') }}">@include('partials.icon', ['name' => 'compass', 'size' => 20]) Explore</a>
      <a class="nav-item" href="{{ route('explore') }}">@include('partials.icon', ['name' => 'users', 'size' => 20]) My Groups</a>
      @auth
        <a class="nav-item" href="{{ route('settings') }}">@include('partials.icon', ['name' => 'settings', 'size' => 20]) Settings</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">@csrf<button class="nav-item logout" type="submit">@include('partials.icon', ['name' => 'logout', 'size' => 20]) Log out</button></form>
      @else
        <a class="nav-item" href="{{ route('login') }}">@include('partials.icon', ['name' => 'settings', 'size' => 20]) Settings</a>
        <a class="nav-item" href="{{ route('login') }}">@include('partials.icon', ['name' => 'logout', 'size' => 20]) Log in</a>
      @endauth
    </nav>

    <div class="side-foot">Privacy • Terms • Copyright • More<br>© 2026 Aura Inc. Minimalist Perfection.</div>
  </aside>

  {{-- ============ CENTRE FEED ============ --}}
  <div class="col-centre">
    @auth
    <div class="card">
      <div class="card-head">
        <h2 class="card-title">@include('partials.icon', ['name' => 'sparkle', 'size' => 18]) Aura Daily Highlights</h2>
        <a class="watch-all" href="{{ route('explore') }}">Watch all</a>
      </div>
      <div class="stories-row">
        <a class="story me" href="{{ route('profile.show', auth()->user()) }}">
          <span class="story-img"><img src="{{ auth()->user()->avatarUrl() }}" alt=""><span class="story-plus">+</span></span>
          <span>My Story</span>
        </a>
        @foreach($storyUsers as $storyUser)
          <a class="story" href="{{ route('profile.show', $storyUser) }}">
            <span class="story-img"><img src="{{ $storyUser->avatarUrl() }}" alt=""></span>
            <span>{{ $storyUser->name }}</span>
          </a>
        @endforeach
      </div>
    </div>

    <div class="card">
      <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="composer-top">
          <img class="avatar" src="{{ auth()->user()->avatarUrl() }}" alt="" style="width:40px;height:40px">
          <textarea id="composer-text" name="body" rows="2" placeholder="Share what's on your mind, {{ auth()->user()->name }}..." required maxlength="280">{{ old('body') }}</textarea>
        </div>
        @error('body')<div class="error">{{ $message }}</div>@enderror
        <div class="composer-foot">
          <div class="composer-tools">
            <label class="tool-btn" for="composer-photo" style="cursor:pointer"><span class="ic-photo">@include('partials.icon', ['name' => 'image', 'size' => 18])</span> Photo</label>
            <input id="composer-photo" type="file" name="image" accept="image/*" style="display:none">
            <button type="button" class="tool-btn" onclick="insertEmoji()"><span class="ic-emoji">@include('partials.icon', ['name' => 'smile', 'size' => 18])</span> Emoji</button>
            <button type="button" class="tool-btn" onclick="insertLocation()"><span class="ic-map">@include('partials.icon', ['name' => 'map-pin', 'size' => 18])</span> Map</button>
          </div>
          <div class="composer-right">
            <span class="char-count" id="char-count">0 / 280</span>
            <button type="submit" class="btn-share">Share @include('partials.icon', ['name' => 'send', 'size' => 16])</button>
          </div>
        </div>
        @error('image')<div class="error">{{ $message }}</div>@enderror
      </form>
    </div>
    @else
    <div class="card">
      <h2>Welcome to Troxela</h2>
      <p class="muted">A quiet, premium social space. <a href="{{ route('register') }}">Create an account</a> or <a href="{{ route('login') }}">log in</a> to post, follow people and message friends.</p>
    </div>
    @endauth

    @forelse($posts as $post)
      @include('partials.post', ['post' => $post])
    @empty
      <div class="card"><p class="muted">No posts yet. Follow some people or be the first to post!</p></div>
    @endforelse
    {{ $posts->links() }}
  </div>

  {{-- ============ RIGHT SIDEBAR ============ --}}
  <aside class="col-right">
    <form class="search-pill" action="{{ route('search') }}" method="GET">
      @include('partials.icon', ['name' => 'search', 'size' => 18])
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Search" aria-label="Search">
    </form>

    <div class="card">
      <div class="card-head" style="margin-bottom:6px">
        <h2 class="card-title">@include('partials.icon', ['name' => 'users', 'size' => 18]) Aura Creators</h2>
      </div>
      @forelse($creators as $creator)
        <div class="creator-row">
          <a href="{{ route('profile.show', $creator) }}"><img class="avatar" src="{{ $creator->avatarUrl() }}" alt="" style="width:44px;height:44px"></a>
          <div class="who">
            <a href="{{ route('profile.show', $creator) }}"><b>{{ $creator->name }}</b></a>
            <span class="sub">{{ '@' . $creator->username }} · {{ $creator->posts_count }} posts</span>
          </div>
          @auth
            @if(auth()->id() !== $creator->id)
              <form method="POST" action="{{ route('users.follow', $creator) }}" style="margin:0">@csrf
                <button class="follow-link {{ auth()->user()->isFollowing($creator) ? 'following' : '' }}" type="submit">{{ auth()->user()->isFollowing($creator) ? 'Following' : 'Follow' }}</button>
              </form>
            @endif
          @else
            <a class="follow-link" href="{{ route('login') }}">Follow</a>
          @endauth
        </div>
      @empty
        <p class="muted small">No creators yet.</p>
      @endforelse
    </div>

    <div class="card">
      <div class="card-head" style="margin-bottom:6px">
        <h2 class="card-title">@include('partials.icon', ['name' => 'trending', 'size' => 18]) Trending Now</h2>
      </div>
      @foreach($trending as $i => $topic)
        <div class="trend-row">
          <span class="trend-num {{ $i === 0 ? 'first' : '' }}">{{ $i + 1 }}</span>
          <div class="who"><b>{{ $topic['label'] }}</b><span>{{ $topic['count'] }} posts</span></div>
          @include('partials.icon', ['name' => 'trending', 'size' => 16])
        </div>
      @endforeach
    </div>

    <div class="card premium-card">
      <div class="premium-label">Premium</div>
      <h3>Go Premium Now</h3>
      <p>Unlock all features and get exclusive access to premium content.</p>
      @auth
        <a class="btn btn-white" href="{{ route('profile.show', auth()->user()) }}">Try Premium</a>
      @else
        <a class="btn btn-white" href="{{ route('register') }}">Try Premium</a>
      @endauth
    </div>
  </aside>
</div>
@endsection
