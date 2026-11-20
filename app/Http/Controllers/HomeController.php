<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if (! $user) {
            $posts = Post::with(['user', 'likes', 'comments.user'])->latest()->paginate(10);
            $storyUsers = User::inRandomOrder()->limit(4)->get();
            $creators = User::withCount('posts')->orderByDesc('posts_count')->limit(4)->get();
            $trending = $this->trendingTopics();
            return view('home', compact('posts', 'storyUsers', 'creators', 'trending'));
        }
        $ids = $user->following()->pluck('users.id')->push($user->id);
        $posts = Post::with(['user', 'likes', 'comments.user'])
            ->whereIn('user_id', $ids)->latest()->paginate(10);
        $suggestions = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $user->following()->pluck('users.id'))
            ->inRandomOrder()->limit(5)->get();
        $storyUsers = $user->following()->limit(4)->get();
        if ($storyUsers->count() < 4) {
            $extra = User::where('id', '!=', $user->id)
                ->whereNotIn('id', $storyUsers->pluck('id'))
                ->inRandomOrder()->limit(4 - $storyUsers->count())->get();
            $storyUsers = $storyUsers->concat($extra);
        }
        $creators = User::withCount('posts')->where('id', '!=', $user->id)
            ->orderByDesc('posts_count')->limit(4)->get();
        $trending = $this->trendingTopics();
        return view('home', compact('posts', 'suggestions', 'storyUsers', 'creators', 'trending'));
    }

    public function explore(): View
    {
        $posts = Post::with(['user', 'likes', 'comments.user'])->latest()->paginate(12);
        return view('explore', compact('posts'));
    }

    /**
     * Trending topics from real post content: #hashtags first, then the most
     * frequent meaningful words. Falls back to a static list when there are
     * not enough posts yet.
     *
     * @return array<int, array{label: string, count: int}>
     */
    protected function trendingTopics(): array
    {
        $bodies = Post::pluck('body');
        $topics = [];

        // Hashtags, e.g. #Laravel
        $tags = [];
        foreach ($bodies as $body) {
            if (preg_match_all('/#([\p{L}\p{N}_]+)/u', $body, $m)) {
                foreach ($m[1] as $tag) {
                    $key = mb_strtolower($tag);
                    $tags[$key] = ($tags[$key] ?? 0) + 1;
                }
            }
        }
        arsort($tags);
        $seen = [];
        foreach ($tags as $tag => $count) {
            $topics[] = ['label' => '#' . ucfirst($tag), 'count' => $count];
            $seen[mb_strtolower($tag)] = true;
        }

        // Frequent meaningful words to fill the list up to 5
        if (count($topics) < 5) {
            $stop = ['this', 'that', 'with', 'from', 'your', 'yours', 'have', 'been', 'will', 'just', 'what', 'when', 'where', 'which', 'about', 'there', 'here', 'they', 'them', 'then', 'than', 'more', 'some', 'like', 'into', 'over', 'after', 'before', 'today', 'every', 'never', 'really', 'great', 'first', 'post', 'welcome', 'hello', 'thanks', 'video', 'check', 'share', 'find', 'here'];
            $words = [];
            foreach ($bodies as $body) {
                $clean = mb_strtolower(preg_replace('/#[\p{L}\p{N}_]+/u', ' ', $body));
                $parts = preg_split('/[^\p{L}\p{N}]+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($parts as $word) {
                    if (mb_strlen($word) < 4 || in_array($word, $stop, true)) {
                        continue;
                    }
                    $words[$word] = ($words[$word] ?? 0) + 1;
                }
            }
            arsort($words);
            foreach ($words as $word => $count) {
                if (isset($seen[$word])) {
                    continue;
                }
                $topics[] = ['label' => ucfirst($word), 'count' => $count];
                $seen[$word] = true;
                if (count($topics) >= 5) {
                    break;
                }
            }
        }

        // Static fallback when the site has almost no content yet
        $fallback = [
            ['label' => 'Laravel', 'count' => 128],
            ['label' => 'AI Tools', 'count' => 96],
            ['label' => 'Football', 'count' => 84],
            ['label' => 'Web Development', 'count' => 61],
            ['label' => 'Messi', 'count' => 47],
        ];
        foreach ($fallback as $item) {
            if (count($topics) >= 5) {
                break;
            }
            $key = mb_strtolower(ltrim($item['label'], '#'));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $topics[] = $item;
        }

        return array_slice($topics, 0, 5);
    }
}
