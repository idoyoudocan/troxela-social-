<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageChatDockTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_send_returns_json_message(): void
    {
        $me = User::factory()->create(['username' => 'alice']);
        $other = User::factory()->create(['username' => 'bob']);

        $response = $this->actingAs($me)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('messages.store', $other), ['body' => 'Hello Bob']);

        $response->assertCreated()
            ->assertJsonPath('message.body', 'Hello Bob')
            ->assertJsonPath('message.mine', true);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $me->id,
            'receiver_id' => $other->id,
            'body' => 'Hello Bob',
        ]);
    }

    public function test_plain_form_send_still_redirects(): void
    {
        $me = User::factory()->create(['username' => 'alice']);
        $other = User::factory()->create(['username' => 'bob']);

        $this->actingAs($me)
            ->post(route('messages.store', $other), ['body' => 'Hello again'])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', ['body' => 'Hello again']);
    }

    public function test_history_returns_latest_messages_oldest_first_and_marks_read(): void
    {
        $me = User::factory()->create(['username' => 'alice']);
        $other = User::factory()->create(['username' => 'bob']);

        Message::create(['sender_id' => $other->id, 'receiver_id' => $me->id, 'body' => 'first']);
        Message::create(['sender_id' => $me->id, 'receiver_id' => $other->id, 'body' => 'second']);
        Message::create(['sender_id' => $other->id, 'receiver_id' => $me->id, 'body' => 'third']);

        $response = $this->actingAs($me)->getJson(route('messages.history', $other));

        $response->assertOk();
        $bodies = collect($response->json('messages'))->pluck('body')->all();
        $this->assertSame(['first', 'second', 'third'], $bodies);
        $this->assertSame(0, Message::where('receiver_id', $me->id)->whereNull('read_at')->count());
    }

    public function test_cannot_message_or_read_history_with_yourself(): void
    {
        $me = User::factory()->create(['username' => 'alice']);

        $this->actingAs($me)->getJson(route('messages.history', $me))->assertForbidden();
        $this->actingAs($me)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('messages.store', $me), ['body' => 'hi'])
            ->assertForbidden();
    }

    public function test_chat_dock_renders_for_logged_in_users_only(): void
    {
        $me = User::factory()->create(['username' => 'alice']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('chat-dock', false);

        $this->actingAs($me)->get(route('home'))
            ->assertOk()
            ->assertSee('chat-dock', false)
            ->assertSee('openChat', false);
    }

    public function test_profile_message_button_opens_the_dock(): void
    {
        $me = User::factory()->create(['username' => 'alice']);
        $other = User::factory()->create(['username' => 'bob']);

        $this->actingAs($me)->get(route('profile.show', $other))
            ->assertOk()
            ->assertSee('chat-open', false)
            ->assertSee('data-user-id="' . $other->id . '"', false)
            ->assertSee('data-user-username="bob"', false);
    }
}
