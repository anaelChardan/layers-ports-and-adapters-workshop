<?php
/**
 * Created by PhpStorm.
 * User: anael
 * Date: 19/06/17
 * Time: 16:18
 */

namespace Meetup\Infrastructure\Notification;

use Bunny\Client;
use Meetup\Application\Notify;
use Meetup\Domain\Model\MeetupScheduled;
use NaiveSerializer\Serializer;

class RabbitMqNotifier implements Notify
{
    public function meetupScheduled(MeetupScheduled $meetupScheduled): void
    {
        $connection = [
            'host' => 'rabbitmq',
            'vhost' => '/',
            'user' => 'guest',
            'password' => 'guest'
        ];

        $client = new Client($connection);
        $client->connect();

        $client->channel()->publish(Serializer::serialize($meetupScheduled));
    }
}