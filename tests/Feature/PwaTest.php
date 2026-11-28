<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_file_has_troxela_fields(): void
    {
        // The web server serves public/ files directly; the test client
        // cannot, so assert on the file itself (serving was verified live).
        $json = json_decode(file_get_contents(public_path('manifest.json')), true);
        $this->assertSame('Troxela', $json['name']);
        $this->assertSame('Troxela', $json['short_name']);
        $this->assertSame('standalone', $json['display']);
        $this->assertSame('/', $json['start_url']);
        $this->assertSame('#059669', $json['theme_color']);
        $sizes = collect($json['icons'])->pluck('sizes')->all();
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);
        $purposes = collect($json['icons'])->pluck('purpose')->all();
        $this->assertContains('maskable', $purposes);
    }

    public function test_service_worker_file_has_versioned_cache_and_chat_bypass(): void
    {
        $content = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString('troxela-v1', $content);
        // Live chat data must never be cached.
        $this->assertStringContainsString('updates|history', $content);
        $this->assertStringContainsString("caches.open(CACHE)", $content);
    }

    public function test_icons_exist_with_correct_sizes(): void
    {
        foreach (['icon-192.png' => 192, 'icon-512.png' => 512, 'icon-512-maskable.png' => 512, 'apple-touch-icon.png' => 180] as $icon => $size) {
            $path = public_path('icons/' . $icon);
            $this->assertFileExists($path);
            [$width, $height] = getimagesize($path);
            $this->assertSame($size, $width);
            $this->assertSame($size, $height);
        }
    }

    public function test_homepage_includes_pwa_head_tags(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('<link rel="manifest" href="/manifest.json">', false);
        $response->assertSee('<meta name="theme-color" content="#059669">', false);
        $response->assertSee('id="pwa-banner"', false);
        $response->assertSee("navigator.serviceWorker.register('/sw.js')", false);
    }
}
