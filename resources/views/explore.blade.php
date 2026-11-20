@extends('layouts.app')
@section('title', 'Explore')
@section('content')
<h1>Explore</h1>
<p class="muted">Recent posts from everyone on Troxela.</p>
@forelse($posts as $post)
  @include('partials.post', ['post' => $post])
@empty
  <div class="card"><p class="muted">No posts yet.</p></div>
@endforelse
{{ $posts->links() }}
@endsection
