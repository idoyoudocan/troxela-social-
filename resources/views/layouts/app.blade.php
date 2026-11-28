<!DOCTYPE html>
<html lang="en" data-theme="{{ request()->cookie('theme', 'light') }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ trim($__env->yieldContent('title')) ?: 'Troxela' }} — Troxela</title>
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicon.png') }}">
@include('partials.pwa-head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>
<header class="site">
  <div class="header-inner">
    <a class="brand" href="{{ route('home') }}">
      <img class="logo-img" src="{{ asset('images/logo-mark.png') }}" alt="Troxela" width="40" height="40">
      <span class="wordmark">Troxela</span>
    </a>
    <nav class="pill-nav" aria-label="Main">
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}" title="Home">@include('partials.icon', ['name' => 'home', 'size' => 20])</a>
      <a href="{{ route('explore') }}" class="{{ request()->routeIs('explore') ? 'active' : '' }}" title="Explore">@include('partials.icon', ['name' => 'star', 'size' => 20])</a>
      <a href="{{ route('explore') }}" title="Shop">@include('partials.icon', ['name' => 'cart', 'size' => 20])</a>
      @auth
        <a href="{{ route('profile.show', auth()->user()) }}" class="{{ request()->routeIs('profile.show') ? 'active' : '' }}" title="My Profile">@include('partials.icon', ['name' => 'briefcase', 'size' => 20])</a>
      @else
        <a href="{{ route('login') }}" title="My Profile">@include('partials.icon', ['name' => 'briefcase', 'size' => 20])</a>
      @endauth
      <a href="{{ route('search') }}" class="{{ request()->routeIs('search') ? 'active' : '' }}" title="Find people">@include('partials.icon', ['name' => 'user-plus', 'size' => 20])</a>
    </nav>
    <div class="header-actions">
      <button class="icon-btn amber" type="button" onclick="toggleTheme()" title="Switch theme">@include('partials.icon', ['name' => 'sun', 'size' => 21])</button>
      @auth
        @php($unreadMsgs = auth()->user()->unreadMessagesCount())
        @php($unreadNotifs = auth()->user()->unreadNotifications()->count())
        <a class="icon-btn" href="{{ route('messages.index') }}" title="Messages">
          @include('partials.icon', ['name' => 'message', 'size' => 21])
          <span class="msg-badge" @if(!$unreadMsgs) style="display:none" @endif>{{ $unreadMsgs }}</span>
        </a>
        <a class="icon-btn" href="{{ route('notifications.index') }}" title="Notifications">
          @include('partials.icon', ['name' => 'bell', 'size' => 21])
          @if($unreadNotifs)<span class="dot"></span>@endif
        </a>
        <a href="{{ route('profile.show', auth()->user()) }}" title="My Profile"><img class="header-avatar" src="{{ auth()->user()->avatarUrl() }}" alt=""></a>
      @else
        <a class="navlink login-link" href="{{ route('login') }}" style="color:var(--text-2);font-size:14px;white-space:nowrap">Log in</a>
        <a class="btn btn-sm" href="{{ route('register') }}">Sign up</a>
      @endauth
    </div>
  </div>
</header>
<main class="page @yield('mainclass')">
  @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
  @yield('content')
</main>
<nav class="mobile-nav" aria-label="Mobile">
  <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">@include('partials.icon', ['name' => 'home', 'size' => 22]) Home</a>
  <a href="{{ route('explore') }}" class="{{ request()->routeIs('explore') ? 'active' : '' }}">@include('partials.icon', ['name' => 'star', 'size' => 22]) Explore</a>
  <a href="{{ route('search') }}" class="{{ request()->routeIs('search') ? 'active' : '' }}">@include('partials.icon', ['name' => 'search', 'size' => 22]) Search</a>
  @auth
    @php($unreadMsgsNav = auth()->user()->unreadMessagesCount())
    <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'message', 'size' => 22]) Messages<span class="msg-badge" @if(!$unreadMsgsNav) style="display:none" @endif>{{ $unreadMsgsNav }}</span></a>
    <a href="{{ route('profile.show', auth()->user()) }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'user', 'size' => 22]) Profile</a>
  @else
    <a href="{{ route('login') }}">@include('partials.icon', ['name' => 'message', 'size' => 22]) Messages</a>
    <a href="{{ route('login') }}">@include('partials.icon', ['name' => 'user', 'size' => 22]) Profile</a>
  @endauth
</nav>
@include('partials.chatbot')
@include('partials.chat-dock')
@include('partials.pwa-banner')
<script>
function toggleTheme(){
  const html=document.documentElement;
  const next=html.getAttribute('data-theme')==='dark'?'light':'dark';
  html.setAttribute('data-theme',next);
  document.cookie='theme='+next+';path=/;max-age=31536000';
}
document.addEventListener('click', function(e){
  const btn = e.target.closest('.copy-link');
  if(btn){
    e.preventDefault();
    const url = btn.dataset.url;
    const done = function(){ btn.querySelector('.copy-label').textContent = 'Link copied'; setTimeout(function(){ btn.querySelector('.copy-label').textContent = 'Copy clean link'; }, 2000); };
    if(navigator.clipboard && navigator.clipboard.writeText){ navigator.clipboard.writeText(url).then(done, done); }
    else { const t=document.createElement('textarea'); t.value=url; document.body.appendChild(t); t.select(); try{ document.execCommand('copy'); }catch(_){} t.remove(); done(); }
  }
  const bm = e.target.closest('.bookmark-btn');
  if(bm){
    bm.classList.toggle('bookmarked');
    bm.querySelector('.bm-label').textContent = bm.classList.contains('bookmarked') ? 'Bookmarked' : 'Add Bookmark';
  }
});
(function(){
  const ta = document.getElementById('composer-text');
  const count = document.getElementById('char-count');
  if(ta && count){
    const update = function(){ count.textContent = ta.value.length + ' / 280'; };
    ta.addEventListener('input', update); update();
  }
  window.insertEmoji = function(){
    if(!ta) return;
    ta.value += '\u{1F60A}';
    ta.dispatchEvent(new Event('input'));
    ta.focus();
  };
  window.insertLocation = function(){
    if(!ta) return;
    ta.value += ' \u{1F4CD} ';
    ta.dispatchEvent(new Event('input'));
    ta.focus();
  };
})();
</script>
</body>
</html>
