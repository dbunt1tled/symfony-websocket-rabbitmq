<?php

namespace App\Command\Amqp;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Components\Helpers\Amqp\AMQPHelper;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\HttpFoundation\Response;

class AmqpConsumerCommand extends Command
{
    protected static $defaultName = 'amqp:consumer';
    /** @var AMQPStreamConnection */
    private $connection;

    public function __construct(AMQPStreamConnection $connection, string $name = null)
    {
        parent::__construct($name);
        $this->connection = $connection;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $connection = $this->connection;
        $channel = $connection->channel();
        AMQPHelper::initNotifications($channel);
        AMQPHelper::registerShutdown($connection, $channel);
        $consumerTag = 'consumer_' . getmypid();
        $channel->basic_consume(AMQPHelper::QUEUE_NOTIFICATIONS, $consumerTag, false, false, false, false, function ($message) use ($output)
        {
            //$output->writeln(print_r(json_decode($message->body, true), true));
            // This is our new stuff
            $context = new \ZMQContext();
            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
            if($socket instanceof \ZMQSocket)
            {
                // Здесь тоже передаём идентификатор, чтобы в push классе мы смогли получить объект topic
                $socket->connect(sprintf('tcp://%s:5555', getenv('APP_HTTP_HOST')));
                // $output->writeln($message->body);
                $socket->send($message->body);
            }
            /** @var AMQPChannel $channel */
            $channel = $message->delivery_info['channel'];
            $channel->basic_ack($message->delivery_info['delivery_tag']);
        });
        while (\count($channel->callbacks)) {
            $channel->wait();
        }

        $io->success('Success.');
    }
}
