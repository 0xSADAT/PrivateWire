<?php

namespace RTippin\Messenger\Tests\Messenger;

use RTippin\Messenger\Contracts\MessengerProvider;
use RTippin\Messenger\Exceptions\InvalidMessengerProvider;
use RTippin\Messenger\Facades\Messenger as MessengerFacade;
use RTippin\Messenger\Messenger;
use RTippin\Messenger\Models\GhostUser;
use RTippin\Messenger\Models\Participant;
use RTippin\Messenger\Tests\MessengerTestCase;
use RTippin\Messenger\Tests\stubs\OtherModel;
use RTippin\Messenger\Tests\stubs\UserModel;

class MessengerTest extends MessengerTestCase
{
    private Messenger $messenger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messenger = app(Messenger::class);
    }

    /** @test */
    public function messenger_facade_same_instance_as_container()
    {
        $this->assertSame($this->messenger, MessengerFacade::instance());
    }

    /** @test */
    public function messenger_helper_same_instance_as_container()
    {
        $this->assertSame($this->messenger, messenger());
    }

    /** @test */
    public function messenger_throws_exception_when_setting_invalid_provider()
    {
        $this->expectException(InvalidMessengerProvider::class);

        $this->messenger->setProvider(new OtherModel);
    }

    /** @test */
    public function messenger_resolves_ghost_user_when_requested()
    {
        $ghost = $this->messenger->getGhostProvider();

        $this->assertInstanceOf(GhostUser::class, $ghost);
    }

    /** @test */
    public function messenger_resolves_ghost_user_once()
    {
        $ghost = $this->messenger->getGhostProvider();

        $this->assertSame($ghost, $this->messenger->getGhostProvider());
        $this->assertSame($ghost, messenger()->getGhostProvider());
        $this->assertSame($ghost, MessengerFacade::getGhostProvider());

        $this->messenger->reset();

        $this->assertNotSame($ghost, $this->messenger->getGhostProvider());
    }

    /** @test */
    public function messenger_resolves_ghost_participant_when_requested()
    {
        $participant = $this->messenger->getGhostParticipant('1234');

        $this->assertInstanceOf(Participant::class, $participant);
    }

    /** @test */
    public function messenger_resolves_ghost_participant_once_unless_thread_id_changes()
    {
        $participant = $this->messenger->getGhostParticipant('1234');

        $this->assertSame($participant, $this->messenger->getGhostParticipant('1234'));
        $this->assertSame($participant, messenger()->getGhostParticipant('1234'));
        $this->assertSame($participant, MessengerFacade::getGhostParticipant('1234'));

        $this->assertNotSame($participant, $this->messenger->getGhostParticipant('5678'));
    }

    /** @test */
    public function messenger_sets_valid_provider()
    {
        $model = $this->getModelUser();

        $provider = new $model([
            'id' => 1,
            'name' => 'Richard Tippin',
            'email' => 'richard.tippin@gmail.com',
            'password' => 'secret',
        ]);

        $this->messenger->setProvider($provider);

        $this->assertSame($provider, $this->messenger->getProvider());
        $this->assertSame('user', $this->messenger->getProviderAlias());
        $this->assertSame(1, $this->messenger->getProviderId());
        $this->assertSame($this->getModelUser(), $this->messenger->getProviderClass());
        $this->assertTrue(app()->bound(MessengerProvider::class));
        $this->assertSame($provider, app(MessengerProvider::class));
        $this->assertTrue($this->messenger->providerHasFriends());
        $this->assertTrue($this->messenger->providerHasDevices());
        $this->assertTrue($this->messenger->isProviderSet());

        $expected = [
            $this->getModelUser(),
            $this->getModelCompany(),
        ];

        $this->assertSame($expected, $this->messenger->getFriendableForCurrentProvider());
        $this->assertSame($expected, $this->messenger->getSearchableForCurrentProvider());
    }

    /** @test */
    public function messenger_unsets_provider()
    {
        $model = $this->getModelUser();

        $provider = new $model([
            'id' => 1,
            'name' => 'Richard Tippin',
            'email' => 'richard.tippin@gmail.com',
            'password' => 'secret',
        ]);

        $this->messenger->setProvider($provider);

        $this->assertSame($provider, $this->messenger->getProvider());

        $this->messenger->unsetProvider();

        $this->assertNull($this->messenger->getProviderAlias());
        $this->assertNull($this->messenger->getProviderId());
        $this->assertNull($this->messenger->getProviderClass());
        $this->assertFalse(app()->bound(MessengerProvider::class));
        $this->assertFalse($this->messenger->providerHasFriends());
        $this->assertFalse($this->messenger->providerHasDevices());
        $this->assertFalse($this->messenger->isProviderSet());
        $this->assertSame([], $this->messenger->getFriendableForCurrentProvider());
        $this->assertSame([], $this->messenger->getSearchableForCurrentProvider());
    }

    /** @test */
    public function messenger_can_get_configs()
    {
        $this->assertSame('Messenger-Testbench', $this->messenger->getSiteName());
        $this->assertSame('default', $this->messenger->getBroadcastDriver());
        $this->assertSame('null', $this->messenger->getVideoDriver());
        $this->assertSame('/messenger', $this->messenger->getWebEndpoint());
        $this->assertSame('/api/messenger', $this->messenger->getApiEndpoint());
        $this->assertSame(config('app.url'), $this->messenger->getSocketEndpoint());
        $this->assertFalse($this->messenger->isProviderSet());
        $this->assertNull($this->messenger->getProvider());
        $this->assertFalse($this->messenger->isPushNotificationsEnabled());
        $this->assertSame('messenger', $this->messenger->getThreadStorage('disk'));
        $this->assertSame('threads', $this->messenger->getThreadStorage('directory'));
        $this->assertSame('public', $this->messenger->getAvatarStorage('disk'));
        $this->assertSame('images', $this->messenger->getAvatarStorage('directory'));
        $this->assertSame(4, $this->messenger->getOnlineCacheLifetime());
        $this->assertTrue($this->messenger->isCallingEnabled());
        $this->assertSame(5, $this->messenger->getKnockTimeout());
        $this->assertTrue($this->messenger->isKnockKnockEnabled());
        $this->assertSame(25, $this->messenger->getSearchPageCount());
        $this->assertSame(25, $this->messenger->getThreadsPageCount());
        $this->assertSame(100, $this->messenger->getThreadsIndexCount());
        $this->assertSame(500, $this->messenger->getParticipantsIndexCount());
        $this->assertSame(50, $this->messenger->getParticipantsPageCount());
        $this->assertSame(50, $this->messenger->getMessagesPageCount());
        $this->assertSame(50, $this->messenger->getMessagesIndexCount());
        $this->assertSame(25, $this->messenger->getCallsIndexCount());
        $this->assertSame(25, $this->messenger->getCallsPageCount());
        $this->assertTrue($this->messenger->isOnlineStatusEnabled());
        $this->assertTrue($this->messenger->isThreadInvitesEnabled());
        $this->assertSame(3, $this->messenger->getThreadMaxInvitesCount());
        $this->assertTrue($this->messenger->isMessageDocumentUploadEnabled());
        $this->assertTrue($this->messenger->isMessageDocumentDownloadEnabled());
        $this->assertTrue($this->messenger->isMessageImageUploadEnabled());
        $this->assertTrue($this->messenger->isThreadAvatarUploadEnabled());
        $this->assertTrue($this->messenger->isProviderAvatarUploadEnabled());
        $this->assertTrue($this->messenger->isProviderAvatarRemovalEnabled());
        $this->assertCount(2, $this->messenger->getMessengerProviders());
    }

    /** @test */
    public function messenger_can_set_configs()
    {
        // Override config values.
        $this->messenger->setPushNotifications(true);
        $this->messenger->setOnlineCacheLifetime(10);
        $this->messenger->setCalling(false);
        $this->messenger->setKnockTimeout(10);
        $this->messenger->setKnockKnock(false);
        $this->messenger->setSearchPageCount(5);
        $this->messenger->setThreadsPageCount(5);
        $this->messenger->setThreadsIndexCount(5);
        $this->messenger->setParticipantsIndexCount(5);
        $this->messenger->setParticipantsPageCount(5);
        $this->messenger->setMessagesPageCount(5);
        $this->messenger->setMessagesIndexCount(5);
        $this->messenger->setCallsIndexCount(5);
        $this->messenger->setCallsPageCount(5);
        $this->messenger->setOnlineStatus(false);
        $this->messenger->setThreadInvites(false);
        $this->messenger->setThreadInvitesMaxCount(5);
        $this->messenger->setMessageDocumentUpload(false);
        $this->messenger->setMessageDocumentDownload(false);
        $this->messenger->setMessageImageUpload(false);
        $this->messenger->setThreadAvatarUpload(false);
        $this->messenger->setProviderAvatarUpload(false);
        $this->messenger->setProviderAvatarRemoval(false);
        $this->messenger->setMessengerProviders([
            'user' => [
                'model' => UserModel::class,
                'searchable' => false,
                'friendable' => false,
                'devices' => false,
                'default_avatar' => '/path/to/user.png',
                'provider_interactions' => [
                    'can_message' => false,
                    'can_search' => false,
                    'can_friend' => false,
                ],
            ],
        ]);

        // Now check values changed.
        $this->assertTrue($this->messenger->isPushNotificationsEnabled());
        $this->assertSame(10, $this->messenger->getOnlineCacheLifetime());
        $this->assertFalse($this->messenger->isCallingEnabled());
        $this->assertSame(10, $this->messenger->getKnockTimeout());
        $this->assertFalse($this->messenger->isKnockKnockEnabled());
        $this->assertSame(5, $this->messenger->getSearchPageCount());
        $this->assertSame(5, $this->messenger->getThreadsPageCount());
        $this->assertSame(5, $this->messenger->getThreadsIndexCount());
        $this->assertSame(5, $this->messenger->getParticipantsIndexCount());
        $this->assertSame(5, $this->messenger->getParticipantsPageCount());
        $this->assertSame(5, $this->messenger->getMessagesPageCount());
        $this->assertSame(5, $this->messenger->getMessagesIndexCount());
        $this->assertSame(5, $this->messenger->getCallsIndexCount());
        $this->assertSame(5, $this->messenger->getCallsPageCount());
        $this->assertFalse($this->messenger->isOnlineStatusEnabled());
        $this->assertFalse($this->messenger->isThreadInvitesEnabled());
        $this->assertSame(5, $this->messenger->getThreadMaxInvitesCount());
        $this->assertFalse($this->messenger->isMessageDocumentUploadEnabled());
        $this->assertFalse($this->messenger->isMessageDocumentDownloadEnabled());
        $this->assertFalse($this->messenger->isMessageImageUploadEnabled());
        $this->assertFalse($this->messenger->isThreadAvatarUploadEnabled());
        $this->assertFalse($this->messenger->isProviderAvatarUploadEnabled());
        $this->assertFalse($this->messenger->isProviderAvatarRemovalEnabled());
        $this->assertCount(1, $this->messenger->getMessengerProviders());
    }
}
