<?php
declare(strict_types = 1);

namespace Meetup\Infrastructure\UserInterface\Cli;

use Meetup\Application\ScheduleMeetupHandler;
use Meetup\Application\ScheduleMeetup;
use Meetup\Domain\Model\MeetupRepository;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\IO\IO;

final class ScheduleMeetupConsoleHandler
{
    /**
     * @var ScheduleMeetupHandler
     */
    private $meetupScheduler;

    /**
     * @var MeetupRepository
     */
    private $repository;

    public function __construct(ScheduleMeetupHandler $meetupScheduler, MeetupRepository $repository)
    {
        $this->meetupScheduler = $meetupScheduler;
        $this->repository = $repository;
    }

    public function handle(Args $args, IO $io): int
    {
        $command = new ScheduleMeetup();
        $command->id = (string)$this->repository->nextIdentity();
        $command->name = $args->getArgument('name');
        $command->description = $args->getArgument('description');
        $command->scheduledFor = $args->getArgument('scheduledFor');

        $errors = $command->validate();

        if (empty($errors)) {
            $this->meetupScheduler->handle($command);

            $io->writeLine('<success>Scheduled the meetup successfully</success>');

            return 0;
        }

        foreach ($errors as $error) {
            $io->error($error);
        }

        return -1;
    }
}
