<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_pagination_renders_when_more_than_one_page(): void
    {
        $user = User::factory()->create();
        for ($i = 1; $i <= 12; $i++) {
            Post::create(['user_id' => $user->id, 'body' => 'পেজিনেশন পরীক্ষার পোস্ট ' . $i]);
        }

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('aria-label="Pagination Navigation"', false);
        $response->assertSee('?page=2', false);
    }

    public function test_pagination_svgs_are_bounded_without_tailwind(): void
    {
        // The site loads no Tailwind, so Laravel's default pagination SVGs
        // (class w-5 h-5, no width/height attributes) rendered 300px+ wide
        // on phones until this CSS bounded them. Guard the rule.
        $css = file_get_contents(public_path('css/app.css'));
        $this->assertStringContainsString('nav[aria-label="Pagination Navigation"] svg{width:18px', $css);
        $this->assertStringContainsString('nav[aria-label="Pagination Navigation"]>div:last-child{display:none}', $css);
    }
}
