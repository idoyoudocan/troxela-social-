<?php

namespace Tests\Feature;

use App\Support\Chatbot;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    public function test_greeting_gets_a_helpful_reply(): void
    {
        $this->postJson(route('chatbot.message'), ['message' => 'হ্যালো'])
            ->assertOk()
            ->assertJsonStructure(['reply'])
            ->assertJsonFragment(['reply' => Chatbot::ruleBasedReply('হ্যালো')]);
    }

    public function test_post_question_explains_how_to_post(): void
    {
        $response = $this->postJson(route('chatbot.message'), ['message' => 'পোস্ট করব কীভাবে?']);

        $response->assertOk();
        $this->assertStringContainsString('২৮০', $response->json('reply'));
    }

    public function test_password_question_points_to_reset(): void
    {
        $response = $this->postJson(route('chatbot.message'), ['message' => 'পাসওয়ার্ড ভুলে গেছি']);

        $response->assertOk();
        $this->assertStringContainsString('Forgot your password?', $response->json('reply'));
    }

    public function test_unknown_question_gets_the_fallback(): void
    {
        $response = $this->postJson(route('chatbot.message'), ['message' => 'আবহাওয়া কেমন?']);

        $response->assertOk();
        $this->assertStringContainsString('পোস্ট করব কীভাবে', $response->json('reply'));
    }

    public function test_message_is_required_and_limited(): void
    {
        $this->postJson(route('chatbot.message'), ['message' => ''])->assertStatus(422);
        $this->postJson(route('chatbot.message'), ['message' => str_repeat('ক', 501)])->assertStatus(422);
    }

    public function test_rule_based_reply_is_used_without_an_api_key(): void
    {
        config(['services.openai.key' => null]);

        $this->assertSame(
            'Troxela একটি সোশ্যাল মিডিয়া ওয়েবসাইট — এখানে পোস্ট লেখা, ছবি শেয়ার করা, বন্ধুদের ফলো করা, মেসেজ পাঠানো, স্টোরি দেখা আর লাইক-কমেন্ট করা যায়। সাইটটি Laravel দিয়ে বানানো।',
            Chatbot::reply('Troxela কি?')
        );
    }
}
