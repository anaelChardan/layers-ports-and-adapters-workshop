<?php
/**
 * Created by PhpStorm.
 * User: anael
 * Date: 19/06/17
 * Time: 12:05
 */

namespace Meetup\Domain\Model;

/**
 * Port Interface
 *
 * Interface MeetupRepository
 * @package Meetup\Domain\Model
 */
interface MeetupRepository
{
    public function add(Meetup $meetup): void;

    public function byId(MeetupId $id): Meetup;

    /**
     * @param \DateTimeImmutable $now
     * @return Meetup[]
     */
    public function upcomingMeetups(\DateTimeImmutable $now): array;

    /**
     * @param \DateTimeImmutable $now
     * @return Meetup[]
     */
    public function pastMeetups(\DateTimeImmutable $now): array;

    public function nextIdentity(): MeetupId;
}