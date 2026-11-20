@extends('layouts.app')
@section('title', 'Edit post')
@section('content')
<div class="card">
  <h1>Edit post</h1>
  <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <label for="body">Post</label>
    <textarea id="body" name="body" rows="4" required maxlength="2000">{{ old('body', $post->body) }}</textarea>
    @error('body')<div class="error">{{ $message }}</div>@enderror
    @if($post->imageUrl())<img class="post-img" src="{{ $post->imageUrl() }}" alt="">@endif
    <label for="image">Replace image (optional)</label>
    <input id="image" type="file" name="image" accept="image/*">
    <div style="margin-top:14px"><button type="submit">Save changes</button></div>
  </form>
</div>
@endsection
