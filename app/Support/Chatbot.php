<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Troxela assistant.
 *
 * Answer order:
 *  1. OpenAI (gpt-4o-mini) when OPENAI_API_KEY is set in .env.
 *  2. Built-in knowledge base (Bangla and English) for known Troxela
 *     topics — fast, accurate, no network needed.
 *  3. A free AI API (Pollinations, no key needed) for anything the
 *     knowledge base does not cover.
 *  4. A generic fallback suggesting example questions.
 *
 * Any AI failure falls back to the next step, so the widget never breaks.
 */
class Chatbot
{
    public static function reply(string $message): string
    {
        $key = config('services.openai.key');

        if (is_string($key) && $key !== '') {
            $ai = self::askOpenAi($message);
            if ($ai !== null) {
                return $ai;
            }
        }

        $rule = self::ruleBasedReply($message);
        if ($rule !== null) {
            return $rule;
        }

        $free = self::askFreeAi($message);
        if ($free !== null) {
            return $free;
        }

        return self::fallbackReply();
    }

    /**
     * Built-in answers about Troxela. Returns null when no topic matches,
     * so the caller can try the free AI instead.
     */
    public static function ruleBasedReply(string $message): ?string
    {
        $text = mb_strtolower(trim($message));

        $has = fn (array $words): bool => collect($words)->contains(
            fn ($w) => str_contains($text, $w)
        );

        if ($has(['hello', 'hi', 'hey', 'সালাম', 'আসসালামু', 'হ্যালো', 'হাই', 'নমস্কার'])) {
            return 'আসসালামু আলাইকুম! আমি Troxela-র সহকারী। পোস্ট করা, মেসেজ পাঠানো, ফলো করা বা পাসওয়ার্ড রিসেট — যেকোনো বিষয়ে জিজ্ঞেস করতে পারেন।';
        }

        if ($has(['ফ্রি', 'free', 'টাকা লাগে', 'খরচ', 'প্রাইস', 'price'])) {
            return 'Troxela সম্পূর্ণ ফ্রি — অ্যাকাউন্ট খোলা, পোস্ট করা, মেসেজ পাঠানো, ফলো করা — সবকিছুর জন্য কোনো টাকা লাগে না।';
        }

        if ($has(['troxela কি', 'what is troxela', 'এই সাইট', 'about troxela', 'troxela সম্পর্কে', 'এই অ্যাপ', 'এই ওয়েবসাইট'])) {
            return 'Troxela একটি সোশ্যাল মিডিয়া ওয়েবসাইট — এখানে পোস্ট লেখা, ছবি শেয়ার করা, বন্ধুদের ফলো করা, মেসেজ পাঠানো, স্টোরি দেখা আর লাইক-কমেন্ট করা যায়। সাইটটি Laravel দিয়ে বানানো।';
        }

        if ($has(['ভিডিও', 'video'])) {
            return 'ভিডিও আপলোডের সুবিধা এখনো নেই — পোস্টে শুধু ছবি দেওয়া যায় (সর্বোচ্চ ৫ MB)। ছবি দিতে পোস্ট লেখার বাক্সের ছবির বোতামটি ব্যবহার করুন।';
        }

        if ($has(['অ্যাকাউন্ট ডিলিট', 'অ্যাকাউন্ট বন্ধ', 'অ্যাকাউন্ট মুছ', 'delete my account', 'delete account', 'close my account'])) {
            return 'অ্যাকাউন্ট নিজে ডিলিট করার বোতাম এখনো নেই। ডিলিট করতে চাইলে Troxela-তে অ্যাডমিনকে মেসেজ করে জানান — তিনিই ব্যবস্থা করে দেবেন।';
        }

        if ($has(['পোস্ট ডিলিট', 'পোস্ট মুছ', 'পোস্ট এডিট', 'পোস্ট বদল', 'delete post', 'edit post', 'পোস্ট ঠিক কর'])) {
            return 'নিজের পোস্ট বদলাতে বা মুছতে পোস্টের ••• মেনুতে চাপুন — সেখান থেকে Edit আর Delete দুটোই করা যায়। হোমপেজের বাক্সে সর্বোচ্চ ২৮০ অক্ষর লেখা যায়, আর Edit পাতায় ২০০০ অক্ষর পর্যন্ত।';
        }

        if ($has(['পোস্ট', 'post', 'লিখব', 'শেয়ার করব', 'status'])) {
            return 'পোস্ট করতে হোমপেজের উপরের বাক্সে লিখুন (সর্বোচ্চ ২৮০ অক্ষর), চাইলে ছবি যোগ করুন (সর্বোচ্চ ৫ MB), তারপর Post বোতাম চাপুন। নিজের পোস্টের ••• মেনু থেকে এডিট, শেয়ার বা ডিলিট করা যায়।';
        }

        if ($has(['এক্সপ্লোর', 'explore'])) {
            return 'Explore পাতায় সবার পোস্ট একসাথে দেখা যায় — নতুন মানুষ আর জনপ্রিয় পোস্ট খুঁজে পাওয়ার সবচেয়ে ভালো জায়গা। উপরের মেনু থেকে Explore-এ যান।';
        }

        if ($has(['প্রোফাইল ছবি', 'ছবি বদল', 'avatar', 'বায়ো', 'bio', 'ইউজারনেম বদল', 'username'])) {
            return 'নিজের প্রোফাইলে গিয়ে Edit profile চাপুন — সেখান থেকে নাম, ইউজারনেম (সর্বোচ্চ ৩০ অক্ষর), বায়ো (সর্বোচ্চ ৫০০ অক্ষর), জন্মতারিখ আর প্রোফাইল ছবি (সর্বোচ্চ ৫ MB) বদলানো যায়।';
        }

        if ($has(['ফলোয়ার', 'follower', 'following', 'কাকে ফলো করি'])) {
            return 'কাকে ফলো করছেন আর কারা আপনাকে ফলো করছে — সেটা দেখতে নিজের প্রোফাইলে গিয়ে Followers বা Following-এ চাপুন।';
        }

        if ($has(['ফলো', 'follow', 'unfollow'])) {
            return 'কাউকে ফলো করতে তার প্রোফাইলে Follow বোতাম চাপুন — তার পোস্ট তখন আপনার হোম ফিডে আসবে। Follow করা বন্ধ করতে একই বোতামে আবার চাপুন।';
        }

        if ($has(['মেসেজ', 'message', 'chat', 'ইনবক্স', 'কথা বলব'])) {
            return 'কাউকে মেসেজ পাঠাতে তার প্রোফাইলে গিয়ে Message বোতাম চাপুন, অথবা উপরের মেসেজ আইকন থেকে Messages পাতায় যান। নতুন মেসেজ এলে ৩ সেকেন্ড পরপর নিজে থেকেই আপডেট হয়।';
        }

        if ($has(['খুঁজ', 'search', 'সার্চ', 'কাউকে পাব'])) {
            return 'উপরের সার্চ আইকনে গিয়ে নাম লিখলেই মানুষ খুঁজে পাবেন। পোস্ট খুঁজতে চাইলে পোস্টের লেখা বা লেখকের নাম দিয়েও খোঁজা যায়।';
        }

        if ($has(['লগইন হচ্ছে না', 'লগইন করতে পারছি না', 'login problem', 'cannot login', "can't login", 'ঢুকতে পারছি না', 'সাইন ইন হচ্ছে না'])) {
            return 'লগইন না হলে প্রথমে ইমেইল আর পাসওয়ার্ড ঠিকভাবে লিখেছেন কি না দেখুন। পাসওয়ার্ড মনে না থাকলে লগইন পাতার "Forgot your password?" লিংক থেকে রিসেট করুন।';
        }

        if ($has(['পাসওয়ার্ড', 'password', 'ভুলে গেছি', 'forgot', 'reset'])) {
            return 'পাসওয়ার্ড ভুলে গেলে লগইন পাতার "Forgot your password?" লিংকে যান — ইমেইলে রিসেট লিংক পাঠানো হবে।';
        }

        if ($has(['অ্যাকাউন্ট', 'account', 'রেজিস্টার', 'register', 'sign up', 'signup', 'সাইন আপ'])) {
            return 'অ্যাকাউন্ট খুলতে উপরের Sign up বোতামে যান — নাম, ইউজারনেম, ইমেইল আর পাসওয়ার্ড দিলেই হবে। তারপর Edit profile থেকে ছবি আর বায়ো বসাতে পারবেন।';
        }

        if ($has(['স্টোরি', 'story', 'stories'])) {
            return 'হোমপেজের উপরে স্টোরিগুলো দেখা যায়। নিজের স্টোরি দিতে "My Story"-তে চাপুন।';
        }

        if ($has(['লাইক', 'like', 'কমেন্ট', 'comment', 'শেয়ার'])) {
            return 'প্রতিটি পোস্টের নিচে Like, Comment আর Share বোতাম আছে। Share চাপলে পোস্টটি আপনার প্রোফাইলে repost হবে। কমেন্টে সর্বোচ্চ ১০০০ অক্ষর লেখা যায়, আর নিজের কমেন্ট ডিলিটও করা যায়।';
        }

        if ($has(['কে বানিয়েছে', 'who built', 'who made', 'developer', 'ডেভেলপার', 'বানিয়েছে'])) {
            return 'Troxela বানিয়েছেন ইমরান হোসেন — Laravel দিয়ে তৈরি এই সাইটে লগইন, পোস্ট, মেসেজ, প্রোফাইল, স্টোরি আর সার্চ — সবকিছু আছে।';
        }

        if ($has(['নোটিফিকেশন', 'notification', 'নোটিশ'])) {
            return 'লাইক, কমেন্ট, ফলো আর মেসেজের নোটিফিকেশন বেল আইকনে দেখা যায়। সব একসাথে পড়া হিসেবে দিতে "Mark all as read" চাপুন।';
        }

        if ($has(['সেটিংস', 'settings', 'প্রোফাইল বদল', 'edit profile'])) {
            return 'প্রোফাইল বদলাতে নিজের প্রোফাইলে গিয়ে Edit profile চাপুন, আর অ্যাকাউন্ট সেটিংস পাবেন /settings পাতায়।';
        }

        if ($has(['মোবাইল অ্যাপ', 'mobile app', 'অ্যাপ ডাউনলোড', 'play store', 'apk'])) {
            return 'Troxela-র কোনো মোবাইল অ্যাপ নেই — ফোনের ব্রাউজারেই সাইটটি পুরোপুরি চলে, মোবাইলের জন্য আলাদা করে সাজানো আছে।';
        }

        if ($has(['কারা দেখতে', 'কে দেখতে পারবে', 'privacy', 'প্রাইভেসি', 'শুধু আমি'])) {
            return 'পোস্টগুলো লগইন করা সবাই হোম ফিড আর Explore-তে দেখতে পারে। মেসেজ শুধু আপনি আর যাকে পাঠিয়েছেন — তিনিই দেখতে পারেন।';
        }

        if ($has(['যোগাযোগ', 'contact', 'সাপোর্ট', 'support', 'সাহায্য', 'help', 'অভিযোগ'])) {
            return 'কোনো সমস্যা হলে এখানেই আমাকে জিজ্ঞেস করুন — আমি Troxela ব্যবহার নিয়ে সাহায্য করি। আর জরুরি কিছু হলে Troxela-তে অ্যাডমিনকে মেসেজ করে জানাতে পারেন।';
        }

        if ($has(['লগআউট', 'logout', 'log out', 'বের হব', 'সাইন আউট'])) {
            return 'লগআউট করতে উপরের মেনু থেকে Logout চাপুন।';
        }

        if ($has(['ধন্যবাদ', 'thanks', 'thank you'])) {
            return 'আপনাকেও ধন্যবাদ! আর কিছু জানতে চাইলে জিজ্ঞেস করুন।';
        }

        return null;
    }

    public static function fallbackReply(): string
    {
        return 'এই বিষয়ে আমি এখনো নিশ্চিত নই। আমি Troxela ব্যবহার নিয়ে সাহায্য করতে পারি — যেমন: পোস্ট করব কীভাবে, মেসেজ পাঠাব কীভাবে, কাউকে ফলো করব কীভাবে, অথবা পাসওয়ার্ড ভুলে গেলে কী করব।';
    }

    /**
     * Free AI answer via Pollinations (https://text.pollinations.ai) —
     * no API key needed. Returns null on any failure so the caller can
     * fall back to the built-in answers.
     */
    private static function askFreeAi(string $message): ?string
    {
        try {
            $prompt = 'You are the helpful assistant of Troxela, a Laravel social media website. '
                .'Users can write posts (max 280 chars on the home composer, with photos up to 5 MB), follow people, '
                .'send direct messages, view stories, like/comment/share posts, and search people and posts. '
                .'Password reset is via the "Forgot your password?" link on the login page. '
                .'Troxela is completely free to use and has no mobile app; it runs in the mobile browser. '
                .'If asked who built Troxela, say Imran Hossain built it with Laravel. '
                .'Answer briefly (2-4 sentences) in the user\'s language (Bangla or English). '
                .'Do not invent features that do not exist. Question: '.$message;

            $response = Http::timeout(15)->get(
                'https://text.pollinations.ai/'.rawurlencode($prompt)
            );

            if (! $response->successful()) {
                return null;
            }

            $reply = trim($response->body());

            if ($reply === '' || mb_strlen($reply) < 3) {
                return null;
            }

            if (mb_strlen($reply) > 800) {
                $reply = mb_substr($reply, 0, 800).'…';
            }

            return $reply;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function askOpenAi(string $message): ?string
    {
        try {
            $response = Http::timeout(20)->withToken(config('services.openai.key'))->post(
                'https://api.openai.com/v1/chat/completions',
                [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are the helpful assistant of Troxela, a Laravel social media website. '
                                .'Users can write posts (max 280 chars, with photos), follow people, send direct messages, '
                                .'view stories, like/comment/share posts, and search people and posts. '
                                .'Password reset is via the "Forgot your password?" link on the login page. '
                                .'Answer briefly in the user\'s language (Bangla or English). '
                                .'If asked who built Troxela, say Imran Hossain built it with Laravel.',
                        ],
                        ['role' => 'user', 'content' => $message],
                    ],
                    'max_tokens' => 300,
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $reply = $response->json('choices.0.message.content');

            return is_string($reply) && trim($reply) !== '' ? trim($reply) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
