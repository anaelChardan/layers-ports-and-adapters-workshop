<?php
/**
 * Created by PhpStorm.
 * User: anael
 * Date: 19/06/17
 * Time: 16:10
 */

namespace Meetup\Application;


use Meetup\Domain\Model\MeetupScheduled;

interface Notify
{
    public function meetupScheduled(MeetupScheduled $meetupScheduled): void;
}