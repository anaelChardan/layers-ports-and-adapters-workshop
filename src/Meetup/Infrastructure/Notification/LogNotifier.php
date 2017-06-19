<?php
/**
 * Created by PhpStorm.
 * User: anael
 * Date: 19/06/17
 * Time: 16:14
 */

namespace Meetup\Infrastructure\Notification;


use Meetup\Application\Notify;
use Meetup\Domain\Model\MeetupScheduled;
use NaiveSerializer\Serializer;

class LogNotifier implements Notify
{
    public function meetupScheduled(MeetupScheduled $meetupScheduled): void
    {
        error_log("Meetup scheduled :". Serializer::serialize($meetupScheduled));
    }
}