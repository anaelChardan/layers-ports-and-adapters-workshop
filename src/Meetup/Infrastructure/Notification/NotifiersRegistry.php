<?php
/**
 * Created by PhpStorm.
 * User: anael
 * Date: 19/06/17
 * Time: 16:22
 */

namespace Meetup\Infrastructure\Notification;


use Meetup\Application\Notify;
use Meetup\Domain\Model\MeetupScheduled;

class NotifiersRegistry implements Notify
{
    protected $notifiers;

    public function __construct(array $notifiers)
    {
        $this->notifiers = $notifiers;
    }

    public function meetupScheduled(MeetupScheduled $meetupScheduled): void
    {
        /** @var Notify $notifier */
        foreach ($this->notifiers as $notifier) {
            $notifier->meetupScheduled($meetupScheduled);
        }
    }
}