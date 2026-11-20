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
        return $this->image ? asset('storage/' . $this->image) : null;
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
