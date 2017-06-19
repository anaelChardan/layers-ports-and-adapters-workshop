<?php
/**
 * Created by PhpStorm.
 * User: anael
 * Date: 19/06/17
 * Time: 16:12
 */

namespace Meetup\Domain\Model;


class MeetupScheduled
{
    protected $meetupId;

    public function __construct(string $meetupId)
    {
        $this->meetupId = $meetupId;
    }

    public function getMeetupId(): string
    {
        return $this->meetupId;
    }
}