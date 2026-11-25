<?php

namespace Tests\Feature;

use App\Support\Chatbot;
use Illuminate\Support\Facades\Http;
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

    public function test_unknown_question_gets_the_fallback_when_free_ai_fails(): void
    {
        Http::fake(['text.pollinations.ai/*' => Http::response('', 500)]);

        $response = $this->postJson(route('chatbot.message'), ['message' => 'আবহাওয়া কেমন?']);

        $response->assertOk();
        $this->assertStringContainsString('পোস্ট করব কীভাবে', $response->json('reply'));
    }

    public function test_free_ai_answers_unknown_question_when_available(): void
    {
        Http::fake(['text.pollinations.ai/*' => Http::response('আজ ঢাকায় রোদেলা আবহাওয়া থাকবে।', 200)]);

        $response = $this->postJson(route('chatbot.message'), ['message' => 'আবহাওয়া কেমন?']);

        $response->assertOk();
        $this->assertStringContainsString('ঢাকায় রোদেলা', $response->json('reply'));
    }

    public function test_new_faq_topics_are_answered_from_the_knowledge_base(): void
    {
        $this->assertStringContainsString('ভিডিও আপলোডের সুবিধা এখনো নেই', Chatbot::ruleBasedReply('ভিডিও দেওয়া যায়?'));
        $this->assertStringContainsString('সম্পূর্ণ ফ্রি', Chatbot::ruleBasedReply('Troxela কি ফ্রি?'));
        $this->assertStringContainsString('Explore পাতায়', Chatbot::ruleBasedReply('Explore কি?'));
        $this->assertStringContainsString('••• মেনু', Chatbot::ruleBasedReply('পোস্ট ডিলিট করব কীভাবে?'));
        $this->assertStringContainsString('অ্যাকাউন্ট নিজে ডিলিট', Chatbot::ruleBasedReply('অ্যাকাউন্ট ডিলিট করতে চাই'));
        $this->assertNull(Chatbot::ruleBasedReply('আবহাওয়া কেমন?'));
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
