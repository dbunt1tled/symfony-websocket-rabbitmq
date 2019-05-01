<?php

namespace App\Command\Amqp;

use App\Components\Helpers\Amqp\AMQPHelper;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AmqpProducerCommand extends Command
{
    protected static $defaultName = 'amqp:producer';
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
        $arg1 = $input->getArgument('userId');

        $io->writeln('<comment>Produce message</comment>');
        $connection = $this->connection;
        $channel = $this->connection->channel();

        AMQPHelper::initNotifications($channel);
        AMQPHelper::registerShutdown($connection, $channel);

        $data = [
            'type' => 'notification',
            'userId' => $input->getArgument('userId'),
            'message' => 'Hello!',
        ];

        $message = new AMQPMessage(
            json_encode($data),
            ['content_type' => 'text/plain']
        );

        $channel->basic_publish($message, AMQPHelper::EXCHANGE_NOTIFICATIONS);
        $io->success('<info>Done!</info>');
    }
}
