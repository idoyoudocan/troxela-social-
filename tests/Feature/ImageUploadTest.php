<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Support\ImageUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private string $mirrorDir = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->mirrorDir = sys_get_temp_dir().'/troxela-mirror-test-'.uniqid();
        mkdir($this->mirrorDir, 0755, true);
        putenv('PUBLIC_STORAGE_MIRROR_PATH='.$this->mirrorDir);
        $_SERVER['PUBLIC_STORAGE_MIRROR_PATH'] = $this->mirrorDir;
        $_ENV['PUBLIC_STORAGE_MIRROR_PATH'] = $this->mirrorDir;
    }

    protected function tearDown(): void
    {
        putenv('PUBLIC_STORAGE_MIRROR_PATH');
        unset($_SERVER['PUBLIC_STORAGE_MIRROR_PATH'], $_ENV['PUBLIC_STORAGE_MIRROR_PATH']);
        $this->removeDir($this->mirrorDir);
        parent::tearDown();
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.'/'.$item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function test_post_upload_stores_file_and_mirrors_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('posts.store'), [
            'body' => 'A post with a photo',
            'image' => UploadedFile::fake()->image('photo.jpg', 800, 600),
        ])->assertRedirect();

        $post = Post::first();
        $this->assertNotNull($post);
        $this->assertNotNull($post->image);
        $this->assertTrue(Storage::disk('public')->exists($post->image));
        $this->assertFileExists($this->mirrorDir.'/'.$post->image);
        // The stored image must be reachable, not hidden by the fallback.
        $this->assertNotNull($post->imageUrl());
        $this->assertStringContainsString('storage/'.$post->image, $post->imageUrl());

        Storage::disk('public')->delete($post->image);
    }

    public function test_large_phone_photo_upload_stores_and_mirrors(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('posts.store'), [
            'body' => 'A post with a big phone photo',
            'image' => UploadedFile::fake()->image('big.jpg')->size(4096),
        ])->assertRedirect();

        $post = Post::first();
        $this->assertNotNull($post);
        $this->assertNotNull($post->image, 'A 4 MB phone photo must upload, not silently drop the image.');
        $this->assertFileExists($this->mirrorDir.'/'.$post->image);

        Storage::disk('public')->delete($post->image);
    }

    public function test_avatar_upload_stores_and_mirrors(): void
    {
        // Factory usernames often contain dots, which the profile update
        // rejects (alpha_dash) — use an explicitly valid username so the
        // test measures the upload, not the username validation.
        $user = User::factory()->create(['username' => 'avataruser']);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => UploadedFile::fake()->image('avatar.jpg')->size(3072),
        ])->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->avatar, 'A 3 MB avatar must upload, not silently drop the picture.');
        $this->assertTrue(Storage::disk('public')->exists($user->avatar));
        $this->assertFileExists($this->mirrorDir.'/'.$user->avatar);
        $this->assertStringContainsString('storage/'.$user->avatar, $user->avatarUrl());

        Storage::disk('public')->delete($user->avatar);
    }

    public function test_oversized_file_fails_loudly_instead_of_silently_dropping(): void
    {
        // Simulates PHP rejecting the upload because it exceeds
        // upload_max_filesize (error UPLOAD_ERR_INI_SIZE): Laravel's
        // validator must report "failed to upload" and no text-only post
        // may be created.
        $fake = UploadedFile::fake()->image('huge.jpg');
        $oversized = new UploadedFile($fake->getPathname(), 'huge.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true);

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('posts.store'), [
            'body' => 'This must not publish without its photo',
            'image' => $oversized,
        ])->assertSessionHasErrors('image');

        $this->assertSame(0, Post::count());
    }

    public function test_delete_removes_the_mirror_copy_too(): void
    {
        $file = UploadedFile::fake()->image('todelete.jpg');
        $path = ImageUpload::store($file, 'posts');

        $this->assertNotNull($path);
        $this->assertFileExists($this->mirrorDir.'/'.$path);

        ImageUpload::delete($path);

        $this->assertFalse(Storage::disk('public')->exists($path));
        $this->assertFileDoesNotExist($this->mirrorDir.'/'.$path);
    }

    public function test_mirror_roots_skip_a_symlinked_public_storage(): void
    {
        // On a standard install public/storage is a symlink to the public
        // disk root: mirroring there would copy files onto themselves, so
        // it must be skipped. The env-configured mirror is still used.
        $roots = ImageUpload::mirrorRoots();

        $this->assertNotContains(realpath(public_path('storage')), array_map(
            static fn ($root) => realpath($root) ?: $root, $roots
        ));
        $this->assertContains($this->mirrorDir, $roots);
    }
}
