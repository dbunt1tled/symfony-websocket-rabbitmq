<?php


namespace App\Services\Notifications;


use App\Components\Helpers\Amqp\AMQPHelper;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class Notifications
{
    /** @var \PhpAmqpLib\Channel\AMQPChannel */
    private $chanel;

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->chanel = $connection->channel();

        AMQPHelper::initNotifications($this->chanel);
        AMQPHelper::registerShutdown($connection, $this->chanel);
    }

    public function send(array $data): void
    {
        $message = new AMQPMessage(
            json_encode($data),
            ['content_type' => 'text/plain']
        );
        $this->chanel->basic_publish($message, AMQPHelper::EXCHANGE_NOTIFICATIONS);
    }
}
