<?php

namespace Meetup\Application;

final class ScheduleMeetup
{
    /**
     * @var string because it should be serializable
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $scheduledFor;

    /**
     * @return array
     */
    public function validate(): array {
        $validationErrors = [];

        if (empty($this->id)) {
            $validationErrors['name'][] = 'You must provide an ID';
        }

        if (empty($this->name)) {
            $validationErrors['name'][] = 'You must provide a name';
        }
        if (empty($this->description)) {
            $validationErrors['description'][] = 'You must provide a description';
        }
        if (empty($this->scheduledFor)) {
            $validationErrors['scheduledFor'][] = 'You must provide a schedule';
        } else {
            try {
                new \DateTimeImmutable($this->scheduledFor);
            } catch (\Throwable $fault) {
                $validationErrors['scheduledFor'][] = 'The Date is not valid';
            }
        }

        return $validationErrors;
    }
}