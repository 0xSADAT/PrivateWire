<?php

namespace RTippin\Messenger\Tests\Http;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use RTippin\Messenger\Broadcasting\CallJoinedBroadcast;
use RTippin\Messenger\Contracts\MessengerProvider;
use RTippin\Messenger\Events\CallJoinedEvent;
use RTippin\Messenger\Models\Call;
use RTippin\Messenger\Models\Thread;
use RTippin\Messenger\Tests\FeatureTestCase;

class JoinCallTest extends FeatureTestCase
{
    private Thread $group;

    private Call $call;

    private MessengerProvider $tippin;

    private MessengerProvider $doe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tippin = $this->userTippin();

        $this->doe = $this->userDoe();

        $this->group = $this->createGroupThread($this->tippin, $this->doe);

        $this->call = $this->createCall($this->group, $this->tippin);
    }

    /** @test */
    public function joining_missing_call_not_found()
    {
        $this->actingAs($this->doe);

        $this->postJson(route('api.messenger.threads.calls.join', [
            'thread' => $this->group->id,
            'call' => '123-456-789',
        ]))
            ->assertNotFound();
    }

    /** @test */
    public function non_participant_forbidden_to_join_call()
    {
        $this->actingAs($this->companyLaravel());

        $this->postJson(route('api.messenger.threads.calls.join', [
            'thread' => $this->group->id,
            'call' => $this->call->id,
        ]))
            ->assertForbidden();
    }

    /** @test */
    public function forbidden_to_join_inactive_call()
    {
        $this->call->update([
            'call_ended' => now(),
        ]);

        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.threads.calls.join', [
            'thread' => $this->group->id,
            'call' => $this->call->id,
        ]))
            ->assertForbidden();
    }

    /** @test */
    public function kicked_participant_forbidden_to_rejoin_call()
    {
        $this->call->participants()->create([
            'owner_id' => $this->doe->getKey(),
            'owner_type' => get_class($this->doe),
            'left_call' => now(),
            'kicked' => true,
        ]);

        $this->actingAs($this->doe);

        $this->postJson(route('api.messenger.threads.calls.join', [
            'thread' => $this->group->id,
            'call' => $this->call->id,
        ]))
            ->assertForbidden();
    }

    /** @test */
    public function participant_can_join_call()
    {
        Event::fake([
            CallJoinedBroadcast::class,
            CallJoinedEvent::class,
        ]);

        $this->actingAs($this->doe);

        $this->postJson(route('api.messenger.threads.calls.join', [
            'thread' => $this->group->id,
            'call' => $this->call->id,
        ]))
            ->assertSuccessful()
            ->assertJson([
                'call_id' => $this->call->id,
                'left_call' => null,
                'owner' => [
                    'name' => 'John Doe',
                ],
            ]);

        $participant = $this->call->participants()
            ->where('owner_id', '=', $this->doe->getKey())
            ->where('owner_type', '=', get_class($this->doe))
            ->first();

        $this->assertTrue(Cache::has("call:{$this->call->id}:{$participant->id}"));

        Event::assertDispatched(function (CallJoinedBroadcast $event) {
            $this->assertContains('private-user.'.$this->doe->getKey(), $event->broadcastOn());
            $this->assertSame($this->call->id, $event->broadcastWith()['id']);
            $this->assertSame($this->group->id, $event->broadcastWith()['thread_id']);

            return true;
        });

        Event::assertDispatched(function (CallJoinedEvent $event) use ($participant) {
            return $participant->id === $event->participant->id;
        });
    }

    /** @test */
    public function participant_can_rejoin_call()
    {
        $this->expectsEvents([
            CallJoinedBroadcast::class,
            CallJoinedEvent::class,
        ]);

        $participant = $this->call->participants()->first();

        $participant->update([
            'left_call' => now(),
        ]);

        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.threads.calls.join', [
            'thread' => $this->group->id,
            'call' => $this->call->id,
        ]))
            ->assertSuccessful();

        $this->assertNull($participant->fresh()->left_call);

        $this->assertTrue(Cache::has("call:{$this->call->id}:{$participant->id}"));
    }
}
