<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Troxela demo assistant.
 *
 * Answers questions about the site from a built-in knowledge base (Bangla
 * and English). When an OpenAI API key is configured
 * (OPENAI_API_KEY in .env), the same question is answered by the model
 * instead, with the site facts as its system prompt; any failure falls
 * back to the built-in answers, so the widget never breaks.
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

        return self::ruleBasedReply($message);
    }

    public static function ruleBasedReply(string $message): string
    {
        $text = mb_strtolower(trim($message));

        $has = fn (array $words): bool => collect($words)->contains(
            fn ($w) => str_contains($text, $w)
        );

        if ($has(['hello', 'hi', 'hey', 'সালাম', 'আসসালামু', 'হ্যালো', 'হাই', 'নমস্কার'])) {
            return 'আসসালামু আলাইকুম! আমি Troxela-র সহকারী। পোস্ট করা, মেসেজ পাঠানো, ফলো করা বা পাসওয়ার্ড রিসেট — যেকোনো বিষয়ে জিজ্ঞেস করতে পারেন।';
        }

        if ($has(['troxela কি', 'what is troxela', 'এই সাইট', 'about troxela', 'troxela সম্পর্কে'])) {
            return 'Troxela একটি সোশ্যাল মিডিয়া ওয়েবসাইট — এখানে পোস্ট লেখা, ছবি শেয়ার করা, বন্ধুদের ফলো করা, মেসেজ পাঠানো, স্টোরি দেখা আর লাইক-কমেন্ট করা যায়। সাইটটি Laravel দিয়ে বানানো।';
        }

        if ($has(['পোস্ট', 'post', 'লিখব', 'শেয়ার করব', 'status'])) {
            return 'পোস্ট করতে হোমপেজের উপরের বাক্সে লিখুন (সর্বোচ্চ ২৮০ অক্ষর), চাইলে ছবি যোগ করুন, তারপর Post বোতাম চাপুন। নিজের পোস্টের ••• মেনু থেকে এডিট, শেয়ার বা ডিলিট করা যায়।';
        }

        if ($has(['মেসেজ', 'message', 'chat', 'ইনবক্স', 'কথা বলব'])) {
            return 'কাউকে মেসেজ পাঠাতে তার প্রোফাইলে গিয়ে Message বোতাম চাপুন, অথবা উপরের মেসেজ আইকন থেকে Messages পাতায় যান। নতুন মেসেজ এলে ৩ সেকেন্ড পরপর নিজে থেকেই আপডেট হয়।';
        }

        if ($has(['ফলো', 'follow', 'unfollow'])) {
            return 'কাউকে ফলো করতে তার প্রোফাইলে Follow বোতাম চাপুন — তার পোস্ট তখন আপনার হোম ফিডে আসবে। Follow করা বন্ধ করতে একই বোতামে আবার চাপুন।';
        }

        if ($has(['খুঁজ', 'search', 'সার্চ', 'কাউকে পাব'])) {
            return 'উপরের সার্চ আইকনে গিয়ে নাম লিখলেই মানুষ খুঁজে পাবেন। পোস্ট খুঁজতে চাইলে পোস্টের লেখা বা লেখকের নাম দিয়েও খোঁজা যায়।';
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
            return 'প্রতিটি পোস্টের নিচে Like, Comment আর Share বোতাম আছে। Share চাপলে পোস্টটি আপনার প্রোফাইলে repost হবে।';
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

        if ($has(['ধন্যবাদ', 'thanks', 'thank you'])) {
            return 'আপনাকেও ধন্যবাদ! আর কিছু জানতে চাইলে জিজ্ঞেস করুন।';
        }

        return 'এই বিষয়ে আমি এখনো নিশ্চিত নই। আমি Troxela ব্যবহার নিয়ে সাহায্য করতে পারি — যেমন: পোস্ট করব কীভাবে, মেসেজ পাঠাব কীভাবে, কাউকে ফলো করব কীভাবে, অথবা পাসওয়ার্ড ভুলে গেলে কী করব।';
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
