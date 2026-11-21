<article class="card post-card">
  <div class="post-head">
    <a href="{{ route('profile.show', $post->user) }}"><img class="avatar" src="{{ $post->user->avatarUrl() }}" alt=""></a>
    <div class="who">
      <a href="{{ route('profile.show', $post->user) }}"><b>{{ $post->user->name }}</b></a>
      <span class="sub">{{ '@' . $post->user->username }} · {{ $post->created_at->diffForHumans() }}</span>
    </div>
    <details class="more-menu">
      <summary aria-label="More options">@include('partials.icon', ['name' => 'more', 'size' => 20])</summary>
      <div class="more-pop">
        <a href="{{ route('posts.show', $post) }}">View post</a>
        <a href="{{ route('profile.show', $post->user) }}">View profile</a>
        @auth
          @if($post->user_id === auth()->id())
            <a href="{{ route('posts.edit', $post) }}">Edit post</a>
            <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">@csrf @method('DELETE')<button class="danger" type="submit">Delete post</button></form>
          @endif
        @endauth
      </div>
    </details>
  </div>
  <div class="post-body">{!! $post->formattedBody() !!}</div>
  @if($post->imageUrl())<img class="post-img" src="{{ $post->imageUrl() }}" alt="">@endif
  <div class="post-actions">
    @auth
      <form method="POST" action="{{ route('posts.like', $post) }}">@csrf
        <button type="submit" class="action-btn {{ $post->isLikedBy(auth()->user()) ? 'liked' : '' }}">@include('partials.icon', ['name' => 'heart', 'size' => 17]) {{ $post->likes->count() }} {{ $post->isLikedBy(auth()->user()) ? 'Liked' : 'Like' }}</button>
      </form>
    @else
      <span class="action-btn">@include('partials.icon', ['name' => 'heart', 'size' => 17]) {{ $post->likes->count() }}</span>
    @endauth
    <a class="action-btn" href="{{ route('posts.show', $post) }}">@include('partials.icon', ['name' => 'message-circle', 'size' => 17]) {{ $post->comments->count() }} Comments</a>
    @auth
      <form method="POST" action="{{ route('posts.share', $post) }}" onsubmit="return confirm('Share this post to your profile?')">@csrf
        <button type="submit" class="action-btn">@include('partials.icon', ['name' => 'share', 'size' => 17]) Share</button>
      </form>
    @endauth
    <button type="button" class="action-btn copy-link" data-url="{{ route('posts.show', $post) }}">@include('partials.icon', ['name' => 'link', 'size' => 17]) <span class="copy-label">Copy clean link</span></button>
    <button type="button" class="action-btn bookmark-btn">@include('partials.icon', ['name' => 'bookmark', 'size' => 17]) <span class="bm-label">Add Bookmark</span></button>
  </div>
</article>
