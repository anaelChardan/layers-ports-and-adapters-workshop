<?php
/**
 * Created by PhpStorm.
 * User: anael
 * Date: 19/06/17
 * Time: 16:29
 */

namespace Meetup\Infrastructure\Notification;


use Meetup\Application\Notify;
use Meetup\Domain\Model\MeetupScheduled;

class DummyNotification implements Notify
{
    public function meetupScheduled(MeetupScheduled $meetupScheduled): void
    {
    }
}