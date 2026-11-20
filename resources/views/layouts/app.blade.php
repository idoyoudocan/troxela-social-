<!DOCTYPE html>
<html lang="en" data-theme="{{ request()->cookie('theme', 'dark') }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ trim($__env->yieldContent('title')) ?: 'Troxela' }} — Troxela</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="site">
  <nav class="nav">
    <a class="logo" href="{{ route('home') }}">Troxela<span>.</span></a>
    <a class="navlink" href="{{ route('home') }}">Home</a>
    <a class="navlink" href="{{ route('explore') }}">Explore</a>
    @auth
      <a class="navlink" href="{{ route('messages.index') }}">Messages</a>
      <a class="navlink" href="{{ route('notifications.index') }}">Notifications
        @if(auth()->user()->unreadNotifications()->count())
          <span class="badge">{{ auth()->user()->unreadNotifications()->count() }}</span>
        @endif
      </a>
    @endauth
    <form class="search-box" action="{{ route('search') }}" method="GET">
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Search people & posts" style="width:180px">
    </form>
    <span class="spacer"></span>
    <button class="theme-toggle btn-sm" type="button" onclick="toggleTheme()">🌙 / ☀️</button>
    @auth
      <a class="navlink" href="{{ route('profile.show', auth()->user()) }}">{{ '@' . auth()->user()->username }}</a>
      <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf<button class="btn-secondary btn-sm" type="submit">Log out</button></form>
    @else
      <a class="navlink" href="{{ route('login') }}">Log in</a>
      <a class="btn btn-sm" href="{{ route('register') }}">Sign up</a>
    @endauth
  </nav>
</header>
<main>
  @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
  @yield('content')
</main>
<footer class="site">Troxela — a quiet, premium social space. Built with Laravel.</footer>
<script>
function toggleTheme(){
  const html=document.documentElement;
  const next=html.getAttribute('data-theme')==='dark'?'light':'dark';
  html.setAttribute('data-theme',next);
  document.cookie='theme='+next+';path=/;max-age=31536000';
}
</script>
</body>
</html>
