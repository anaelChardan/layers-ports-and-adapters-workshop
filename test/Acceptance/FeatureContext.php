<?php

namespace Tests\Acceptance;

use Behat\Behat\Context\Context;
use Meetup\Application\ScheduleMeetup;
use Meetup\Application\ScheduleMeetupHandler;
use Meetup\Domain\Model\Meetup;
use Meetup\Infrastructure\Notification\DummyNotification;
use Meetup\Infrastructure\Persistence\InMemory\InMemoryMeetupRepository;

/**
 * Defines application features from the specific context.
 */
final class FeatureContext implements Context
{
    protected $inMemoryRepository;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->inMemoryRepository = new InMemoryMeetupRepository();
    }

    /**
     * @When /^I schedule a "([^"]*)" with the description "([^"]*)" on "([^"]*)"$/
     */
    public function iScheduleAWithTheDescriptionOn($name, $description, $scheduledFor)
    {
        $handler = new ScheduleMeetupHandler($this->inMemoryRepository, new DummyNotification());

        $command = new ScheduleMeetup();
        $command->id = (string)$this->inMemoryRepository->nextIdentity();
        $command->name = $name;
        $command->description = $description;
        $command->scheduledFor = $scheduledFor;

        $errors = $command->validate();

        if (!empty($errors)) {
            throw new \InvalidArgumentException($errors);
        }

        $handler->handle($command);
    }

    /**
     * @Then /^there will be an upcoming meetup called "([^"]*)" scheduled for "([^"]*)"$/
     */
    public function thereWillBeAnUpcomingMeetupCalledScheduledFor($name, $scheduledFor)
    {
        $upcomingMeetups = $this->inMemoryRepository->upcomingMeetups(new \DateTimeImmutable('2017-05-02'));

        $meetup = array_filter($upcomingMeetups, function (Meetup $meetup) use ($name, $scheduledFor) {
            return ((string)$meetup->name() === $name && $meetup->scheduledFor() == new \DateTimeImmutable($scheduledFor));
        });

        if (!empty($meetup)) {
            return;
        }

        throw new \RuntimeException('We found no upcoming meetups matching the arguments');
    }
}
