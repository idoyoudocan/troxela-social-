<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Store an uploaded image on the public disk, safely.
 *
 * A file that passed validation can still be unusable at store time: the
 * PHP temp file may be gone or unresolvable on some hosts, in which case
 * SplFileInfo::getRealPath() returns false and Laravel's
 * FilesystemAdapter::putFileAs() crashes with
 * ValueError "Path cannot be empty" (fopen(false) coerces to fopen('')).
 * This helper checks the file is really readable before storing, makes
 * sure the target directory exists, and returns null instead of throwing
 * so controllers can show a friendly error.
 *
 * Mirroring — why uploads can "succeed" yet never show up:
 * User::avatarUrl() / Post::imageUrl() only serve a stored file when it
 * is reachable under public/storage, and the web server only serves what
 * is under its document root. On a standard install public/storage is a
 * symlink to storage/app/public, so storing on the public disk is enough.
 * On this project's shared host (troxela.store) there is no shell access
 * and no storage:link: the app lives in troxela_social while the document
 * root is the sibling public_html, and public_html/storage is a static
 * copy that is never updated by uploads. A file stored in
 * storage/app/public therefore never appears on the site — the post
 * publishes text-only and the avatar falls back to its initial letter,
 * with no error anywhere. To fix that, every stored file is also copied
 * ("mirrored") into each web-served storage folder that actually exists
 * on the host. Mirroring never fails the upload: a mirror failure is
 * only logged. On a standard install the mirror roots are symlinks (or
 * absent), so nothing is copied and behaviour is unchanged.
 */
class ImageUpload
{
    public static function store(UploadedFile $file, string $directory, string $disk = 'public'): ?string
    {
        if (! $file->isValid()) {
            return null;
        }

        $realPath = $file->getRealPath();

        if (! is_string($realPath) || $realPath === '' || ! is_file($realPath)) {
            Log::warning('Image upload skipped: uploaded temp file is not readable.', [
                'directory' => $directory,
                'error' => $file->getError(),
                'original_name' => $file->getClientOriginalName(),
            ]);

            return null;
        }

        try {
            Storage::disk($disk)->makeDirectory($directory);

            $path = $file->store($directory, $disk);

            if (! is_string($path) || $path === '') {
                return null;
            }

            self::mirror($path, $disk);

            return $path;
        } catch (\Throwable $e) {
            Log::warning('Image upload failed while storing.', [
                'directory' => $directory,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete a stored file from the public disk and from every mirror
     * folder, so a replaced/deleted image leaves no stale copy behind
     * that the site could still serve.
     */
    public static function delete(?string $path, string $disk = 'public'): void
    {
        if (! $path) {
            return;
        }

        try {
            Storage::disk($disk)->delete($path);
        } catch (\Throwable $e) {
            Log::warning('Image delete failed on the public disk.', [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);
        }

        self::deleteMirror($path);
    }

    /**
     * Copy a stored file into every web-served storage folder that exists
     * on this host. Failures are logged only — the upload already
     * succeeded and must not be undone by a mirror problem.
     */
    public static function mirror(string $path, string $disk = 'public'): void
    {
        try {
            $source = Storage::disk($disk)->path($path);
        } catch (\Throwable $e) {
            return;
        }

        if (! is_file($source)) {
            return;
        }

        foreach (self::mirrorRoots() as $root) {
            $target = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));

            try {
                $dir = dirname($target);

                if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
                    continue;
                }

                if (! copy($source, $target)) {
                    Log::warning('Image mirror copy failed.', ['path' => $path, 'target' => $target]);
                }
            } catch (\Throwable $e) {
                Log::warning('Image mirror failed.', [
                    'path' => $path,
                    'target' => $target,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    public static function deleteMirror(string $path): void
    {
        foreach (self::mirrorRoots() as $root) {
            $target = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));

            try {
                if (is_file($target)) {
                    unlink($target);
                }
            } catch (\Throwable $e) {
                Log::warning('Image mirror delete failed.', [
                    'path' => $path,
                    'target' => $target,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Web-served storage folders that are real directories on this host
     * and are NOT the public disk itself.
     *
     * - public/storage when it is a real directory (split layout, no
     *   symlink): this is what avatarUrl()/imageUrl() check with is_file().
     * - PUBLIC_STORAGE_MIRROR_PATH from the environment, when set: an
     *   explicit extra served folder (on troxela.store this is the
     *   document-root copy, /home/troxelas/public_html/storage).
     * - <app-parent>/public_html/storage when it exists: the document
     *   root of the split layout used by this project's host, auto-detected
     *   so no configuration is needed there.
     *
     * A root that resolves (via realpath) to the public disk's own root —
     * e.g. public/storage as a symlink to storage/app/public on a standard
     * install — is skipped, so nothing is ever copied onto itself.
     *
     * @return list<string>
     */
    public static function mirrorRoots(): array
    {
        $diskRoot = realpath(Storage::disk('public')->path('')) ?: null;

        $candidates = [
            public_path('storage'),
            is_string(env('PUBLIC_STORAGE_MIRROR_PATH')) && env('PUBLIC_STORAGE_MIRROR_PATH') !== ''
                ? env('PUBLIC_STORAGE_MIRROR_PATH')
                : null,
            dirname(public_path(), 2).DIRECTORY_SEPARATOR.'public_html'.DIRECTORY_SEPARATOR.'storage',
        ];

        $roots = [];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || $candidate === '' || ! is_dir($candidate)) {
                continue;
            }

            $real = realpath($candidate) ?: $candidate;

            if ($diskRoot !== null && $real === $diskRoot) {
                continue;
            }

            $roots[$real] = $candidate;
        }

        return array_values($roots);
    }
}
