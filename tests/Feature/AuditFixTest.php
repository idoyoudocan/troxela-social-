<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditFixTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/settings')->assertOk()->assertSee('Settings');
    }

    public function test_home_page_shows_share_button_and_settings_link(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();
        Post::create(['user_id' => $author->id, 'body' => 'A post on the feed']);
        $this->actingAs($user)->get('/')->assertOk()->assertSee('Share')->assertSee('/settings');
    }

    public function test_search_finds_posts_by_author_name(): void
    {
        $author = User::factory()->create(['name' => 'Imran Hossain']);
        Post::create(['user_id' => $author->id, 'body' => 'Hello world, no name here']);
        $other = User::factory()->create(['name' => 'Someone Else']);
        Post::create(['user_id' => $other->id, 'body' => 'Unrelated post']);

        $response = $this->get('/search?q=Imran');
        $response->assertOk();
        $this->assertCount(1, $response->viewData('posts'));
        $this->assertSame($author->id, $response->viewData('posts')[0]->user_id);
    }

    public function test_share_creates_a_repost(): void
    {
        $author = User::factory()->create();
        $sharer = User::factory()->create();
        $post = Post::create(['user_id' => $author->id, 'body' => 'Original body']);

        $this->actingAs($sharer)->post(route('posts.share', $post))->assertRedirect();
        $this->assertDatabaseHas('posts', ['user_id' => $sharer->id, 'body' => 'Original body']);
        $this->assertSame(2, Post::count());
    }

    public function test_missing_avatar_falls_back_to_initial_letter(): void
    {
        $user = User::factory()->make(['name' => 'Imran Hossain', 'avatar' => 'avatars/does-not-exist.png']);
        $this->assertStringStartsWith('data:image/svg+xml', $user->avatarUrl());
        $this->assertStringNotContainsString('storage/', $user->avatarUrl());
    }

    public function test_missing_post_image_is_omitted(): void
    {
        $post = new Post(['image' => 'posts/does-not-exist.png']);
        $this->assertNull($post->imageUrl());
    }
}
