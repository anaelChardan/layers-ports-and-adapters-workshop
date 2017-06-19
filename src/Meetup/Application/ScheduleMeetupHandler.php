<?php

namespace Meetup\Application;

use Meetup\Domain\Model\Description;
use Meetup\Domain\Model\Meetup;
use Meetup\Domain\Model\MeetupId;
use Meetup\Domain\Model\MeetupRepository;
use Meetup\Domain\Model\MeetupScheduled;
use Meetup\Domain\Model\Name;

class ScheduleMeetupHandler
{
    /**
     * @var MeetupRepository
     */
    protected $repository;

    /**
     * @var Notify
     */
    private $notify;

    /**
     * @param MeetupRepository $repository
     * @param Notify $notify
     */
    public function __construct(MeetupRepository $repository, Notify $notify)
    {
        $this->repository = $repository;
        $this->notify = $notify;
    }

    /**
     * @param ScheduleMeetup $command
     *
     * @return Meetup
     */
    public function handle(ScheduleMeetup $command): Meetup
    {
        $meetup = Meetup::schedule(
            MeetupId::fromString($command->id),
            Name::fromString($command->name),
            Description::fromString($command->description),
            new \DateTimeImmutable($command->scheduledFor)
        );

        $this->repository->add($meetup);

        $this->notify->meetupScheduled(new MeetupScheduled($meetup->meetupId()));

        return $meetup;
    }
}