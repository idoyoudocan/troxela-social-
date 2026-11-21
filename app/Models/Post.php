<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'body', 'image'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function likes(): HasMany { return $this->hasMany(Like::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class)->latest(); }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function imageUrl(): ?string
    {
        // As with User::avatarUrl(): a stored path whose file is missing
        // (never copied to the host, or no public/storage link) must not
        // render a broken <img> — return null so the image is omitted.
        if (! $this->image) {
            return null;
        }
        $path = ltrim($this->image, '/');
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $this->image;
        }
        return is_file(public_path('storage/' . $path)) ? asset('storage/' . $path) : null;
    }

    /** Body HTML with the text escaped and #hashtags wrapped in teal spans. */
    public function formattedBody(): string
    {
        return (string) preg_replace(
            '/#([\p{L}\p{N}_]+)/u',
            '<span class="hashtag">#$1</span>',
            e($this->body)
        );
    }
}
