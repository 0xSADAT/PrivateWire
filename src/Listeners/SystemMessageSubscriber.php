<?php

namespace RTippin\Messenger\Listeners;

use Illuminate\Events\Dispatcher;
use RTippin\Messenger\Events\BotArchivedEvent;
use RTippin\Messenger\Events\BotAvatarEvent;
use RTippin\Messenger\Events\BotUpdatedEvent;
use RTippin\Messenger\Events\CallEndedEvent;
use RTippin\Messenger\Events\DemotedAdminEvent;
use RTippin\Messenger\Events\InviteUsedEvent;
use RTippin\Messenger\Events\NewBotEvent;
use RTippin\Messenger\Events\PackagedBotInstalledEvent;
use RTippin\Messenger\Events\ParticipantsAddedEvent;
use RTippin\Messenger\Events\PromotedAdminEvent;
use RTippin\Messenger\Events\RemovedFromThreadEvent;
use RTippin\Messenger\Events\ThreadArchivedEvent;
use RTippin\Messenger\Events\ThreadAvatarEvent;
use RTippin\Messenger\Events\ThreadLeftEvent;
use RTippin\Messenger\Events\ThreadSettingsEvent;
use RTippin\Messenger\Facades\Messenger;
use RTippin\Messenger\Jobs\BotAddedMessage;
use RTippin\Messenger\Jobs\BotAvatarMessage;
use RTippin\Messenger\Jobs\BotInstalledMessage;
use RTippin\Messenger\Jobs\BotNameMessage;
use RTippin\Messenger\Jobs\BotRemovedMessage;
use RTippin\Messenger\Jobs\CallEndedMessage;
use RTippin\Messenger\Jobs\DemotedAdminMessage;
use RTippin\Messenger\Jobs\JoinedWithInviteMessage;
use RTippin\Messenger\Jobs\ParticipantsAddedMessage;
use RTippin\Messenger\Jobs\PromotedAdminMessage;
use RTippin\Messenger\Jobs\RemovedFromThreadMessage;
use RTippin\Messenger\Jobs\ThreadArchivedMessage;
use RTippin\Messenger\Jobs\ThreadAvatarMessage;
use RTippin\Messenger\Jobs\ThreadLeftMessage;
use RTippin\Messenger\Jobs\ThreadNameMessage;

class SystemMessageSubscriber
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param  Dispatcher  $events
     * @return void
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(CallEndedEvent::class, [SystemMessageSubscriber::class, 'callEndedMessage']);
        $events->listen(DemotedAdminEvent::class, [SystemMessageSubscriber::class, 'demotedAdminMessage']);
        $events->listen(InviteUsedEvent::class, [SystemMessageSubscriber::class, 'joinedWithInviteMessage']);
        $events->listen(ParticipantsAddedEvent::class, [SystemMessageSubscriber::class, 'participantsAddedMessage']);
        $events->listen(PromotedAdminEvent::class, [SystemMessageSubscriber::class, 'promotedAdminMessage']);
        $events->listen(RemovedFromThreadEvent::class, [SystemMessageSubscriber::class, 'removedFromThreadMessage']);
        $events->listen(ThreadArchivedEvent::class, [SystemMessageSubscriber::class, 'threadArchivedMessage']);
        $events->listen(ThreadAvatarEvent::class, [SystemMessageSubscriber::class, 'threadAvatarMessage']);
        $events->listen(ThreadLeftEvent::class, [SystemMessageSubscriber::class, 'threadLeftMessage']);
        $events->listen(ThreadSettingsEvent::class, [SystemMessageSubscriber::class, 'threadNameMessage']);
        $events->listen(NewBotEvent::class, [SystemMessageSubscriber::class, 'botAddedMessage']);
        $events->listen(BotUpdatedEvent::class, [SystemMessageSubscriber::class, 'botNameMessage']);
        $events->listen(BotAvatarEvent::class, [SystemMessageSubscriber::class, 'botAvatarMessage']);
        $events->listen(BotArchivedEvent::class, [SystemMessageSubscriber::class, 'botRemovedMessage']);
        $events->listen(PackagedBotInstalledEvent::class, [SystemMessageSubscriber::class, 'botInstalledMessage']);
    }

    /**
     * @param  CallEndedEvent  $event
     */
    public function callEndedMessage(CallEndedEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? CallEndedMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : CallEndedMessage::dispatchSync($event);
        }
    }

    /**
     * @param  DemotedAdminEvent  $event
     */
    public function demotedAdminMessage(DemotedAdminEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? DemotedAdminMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : DemotedAdminMessage::dispatchSync($event);
        }
    }

    /**
     * @param  InviteUsedEvent  $event
     */
    public function joinedWithInviteMessage(InviteUsedEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? JoinedWithInviteMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : JoinedWithInviteMessage::dispatchSync($event);
        }
    }

    /**
     * @param  ParticipantsAddedEvent  $event
     */
    public function participantsAddedMessage(ParticipantsAddedEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? ParticipantsAddedMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : ParticipantsAddedMessage::dispatchSync($event);
        }
    }

    /**
     * @param  PromotedAdminEvent  $event
     */
    public function promotedAdminMessage(PromotedAdminEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? PromotedAdminMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : PromotedAdminMessage::dispatchSync($event);
        }
    }

    /**
     * @param  RemovedFromThreadEvent  $event
     */
    public function removedFromThreadMessage(RemovedFromThreadEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? RemovedFromThreadMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : RemovedFromThreadMessage::dispatchSync($event);
        }
    }

    /**
     * @param  ThreadArchivedEvent  $event
     */
    public function threadArchivedMessage(ThreadArchivedEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? ThreadArchivedMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : ThreadArchivedMessage::dispatchSync($event);
        }
    }

    /**
     * @param  ThreadAvatarEvent  $event
     */
    public function threadAvatarMessage(ThreadAvatarEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? ThreadAvatarMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : ThreadAvatarMessage::dispatchSync($event);
        }
    }

    /**
     * @param  ThreadLeftEvent  $event
     */
    public function threadLeftMessage(ThreadLeftEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? ThreadLeftMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : ThreadLeftMessage::dispatchSync($event);
        }
    }

    /**
     * @param  ThreadSettingsEvent  $event
     */
    public function threadNameMessage(ThreadSettingsEvent $event): void
    {
        if ($this->isEnabled() && $event->nameChanged) {
            Messenger::getSystemMessageSubscriber('queued')
                ? ThreadNameMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : ThreadNameMessage::dispatchSync($event);
        }
    }

    /**
     * @param  NewBotEvent  $event
     */
    public function botAddedMessage(NewBotEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? BotAddedMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : BotAddedMessage::dispatchSync($event);
        }
    }

    /**
     * @param  BotUpdatedEvent  $event
     */
    public function botNameMessage(BotUpdatedEvent $event): void
    {
        if ($this->isEnabled() && $event->originalName !== $event->bot->name) {
            Messenger::getSystemMessageSubscriber('queued')
                ? BotNameMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : BotNameMessage::dispatchSync($event);
        }
    }

    /**
     * @param  BotAvatarEvent  $event
     */
    public function botAvatarMessage(BotAvatarEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? BotAvatarMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : BotAvatarMessage::dispatchSync($event);
        }
    }

    /**
     * @param  BotArchivedEvent  $event
     */
    public function botRemovedMessage(BotArchivedEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? BotRemovedMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : BotRemovedMessage::dispatchSync($event);
        }
    }

    /**
     * @param  PackagedBotInstalledEvent  $event
     */
    public function botInstalledMessage(PackagedBotInstalledEvent $event): void
    {
        if ($this->isEnabled()) {
            Messenger::getSystemMessageSubscriber('queued')
                ? BotInstalledMessage::dispatch($event)->onQueue(Messenger::getSystemMessageSubscriber('channel'))
                : BotInstalledMessage::dispatchSync($event);
        }
    }

    /**
     * @return bool
     */
    private function isEnabled(): bool
    {
        return Messenger::getSystemMessageSubscriber('enabled');
    }
}
