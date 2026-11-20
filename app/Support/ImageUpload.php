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

            return $path;
        } catch (\Throwable $e) {
            Log::warning('Image upload failed while storing.', [
                'directory' => $directory,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
