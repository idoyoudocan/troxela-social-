<?php
namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo account. Password is a placeholder — change it after first login
        // (or set a real one via the profile / a password reset). Do NOT use
        // 'demo-password-123' in production.
        $imran = User::create([
            'name' => 'Imran Hossain',
            'username' => 'idoyoudocan',
            'email' => 'idoyoudocan@gmail.com',
            'password' => Hash::make('demo-password-123'),
            'bio' => 'Laravel developer · AI tools · Football lover. Building Troxela, one post at a time.',
            'date_of_birth' => '1990-08-15',
        ]);

        $people = [
            ['Ayesha Rahman', 'ayesha', 'ayesha@example.com', 'Laravel learner · Dhaka'],
            ['Tanvir Hasan', 'tanvir', 'tanvir@example.com', 'Football fan · Messi forever'],
            ['Nusrat Jahan', 'nusrat', 'nusrat@example.com', 'AI tools explorer'],
            ['Rahim Uddin', 'rahim', 'rahim@example.com', 'Web developer'],
        ];
        $users = collect($people)->map(fn ($p) => User::create([
            'name' => $p[0], 'username' => $p[1], 'email' => $p[2],
            'password' => Hash::make('demo-password-123'), 'bio' => $p[3],
        ]));
        $users->push($imran);

        // Follows: everyone follows Imran, Imran follows Ayesha and Tanvir
        foreach ($users->where('id', '!=', $imran->id) as $u) {
            $u->following()->attach($imran->id);
        }
        $imran->following()->attach([$users[0]->id, $users[1]->id]);

        $posts = [
            [$imran, 'Welcome to Troxela! This is my first post on the new social site. Laravel, AI tools and football — that is what you will find here.'],
            [$imran, 'Just built a login app in Laravel in 1 minute. Auth::attempt() does the heavy lifting — routes, controller, Blade view, done.'],
            [$users[0], 'Learning Laravel step by step. Today: migrations and Eloquent relationships. Tomorrow: the whole social feed!'],
            [$users[1], 'Messi highlights never get old. 2018 was a special season. Who is your GOAT?'],
            [$users[2], '5 free AI tools I use every day — the full list is on my YouTube channel. Which one should I review next?'],
            [$users[3], 'Dark mode + Instrument Sans + emerald accent = the Troxela look. Clean and calm.'],
            [$users[0], 'Stoic wisdom for the day: focus on what you can control, let go of the rest.'],
            [$users[2], 'Tried a new AI image tool today. The results are scary good.'],
        ];
        $created = collect($posts)->map(fn ($p) => Post::create(['user_id' => $p[0]->id, 'body' => $p[1]]));

        // Likes and comments so the feed feels alive
        $created[0]->likes()->create(['user_id' => $users[0]->id]);
        $created[0]->likes()->create(['user_id' => $users[1]->id]);
        $created[0]->likes()->create(['user_id' => $users[2]->id]);
        $created[1]->likes()->create(['user_id' => $users[0]->id]);
        $created[1]->likes()->create(['user_id' => $users[3]->id]);
        $created[3]->likes()->create(['user_id' => $imran->id]);
        $created[4]->likes()->create(['user_id' => $imran->id]);

        Comment::create(['user_id' => $users[0]->id, 'post_id' => $created[0]->id, 'body' => 'Welcome! Great to see Troxela live.']);
        Comment::create(['user_id' => $users[1]->id, 'post_id' => $created[0]->id, 'body' => 'Following you — keep the Laravel tutorials coming!']);
        Comment::create(['user_id' => $imran->id, 'post_id' => $created[3]->id, 'body' => '100% agree. 2018 Messi was unreal.']);
        Comment::create(['user_id' => $users[2]->id, 'post_id' => $created[1]->id, 'body' => 'That 1-minute login video was so clear. More like this please!']);
    }
}
