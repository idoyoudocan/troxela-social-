<article class="card">
  <div class="post-head">
    <a href="{{ route('profile.show', $post->user) }}"><img class="avatar" src="{{ $post->user->avatarUrl() }}" alt=""></a>
    <div class="who">
      <a href="{{ route('profile.show', $post->user) }}"><b>{{ $post->user->name }}</b></a>
      <span class="muted small">{{ '@' . $post->user->username }} · {{ $post->created_at->diffForHumans() }}</span>
    </div>
    @auth
      @if($post->user_id === auth()->id())
        <span style="margin-left:auto">
          <a class="small" href="{{ route('posts.edit', $post) }}">Edit</a>
          <form method="POST" action="{{ route('posts.destroy', $post) }}" style="display:inline" onsubmit="return confirm('Delete this post?')">@csrf @method('DELETE')<button class="btn-danger btn-sm" type="submit">Delete</button></form>
        </span>
      @endif
    @endauth
  </div>
  <div class="post-body">{{ $post->body }}</div>
  @if($post->imageUrl())<img class="post-img" src="{{ $post->imageUrl() }}" alt="">@endif
  <div class="post-actions">
    @auth
      <form method="POST" action="{{ route('posts.like', $post) }}">@csrf
        <button type="submit" class="{{ $post->isLikedBy(auth()->user()) ? 'liked' : '' }}">♥ {{ $post->likes->count() }} {{ $post->isLikedBy(auth()->user()) ? 'Liked' : 'Like' }}</button>
      </form>
    @else
      <span>♥ {{ $post->likes->count() }}</span>
    @endauth
    <a href="{{ route('posts.show', $post) }}">💬 {{ $post->comments->count() }} Comments</a>
  </div>
</article>
