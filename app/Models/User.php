<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'username', 'email', 'password', 'bio', 'avatar', 'date_of_birth'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    public function posts(): HasMany { return $this->hasMany(Post::class); }
    public function likes(): HasMany { return $this->hasMany(Like::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }

    /** Users this user follows. */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    /** Users who follow this user. */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function sentMessages(): HasMany { return $this->hasMany(Message::class, 'sender_id'); }
    public function receivedMessages(): HasMany { return $this->hasMany(Message::class, 'receiver_id'); }

    public function unreadMessagesCount(): int
    {
        return $this->receivedMessages()->whereNull('read_at')->count();
    }

    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        // Local initial avatar (no external service needed): emerald circle with first letter
        $initial = strtoupper(mb_substr(trim($this->name), 0, 1, 'UTF-8'));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128"><rect width="128" height="128" fill="#10B981"/><text x="64" y="84" font-family="sans-serif" font-size="64" font-weight="bold" fill="#ffffff" text-anchor="middle">' . htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') . '</text></svg>';
        return 'data:image/svg+xml,' . rawurlencode($svg);
    }

    public function getRouteKeyName(): string { return 'username'; }
}
