<?php

namespace App\Command;

use App\Components\Messenger\Pusher;
use App\Components\Messenger\SocketMessenger;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Session\SessionProvider;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\ZMQ\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class SocketWServerCommand extends Command
{
    protected static $defaultName = 'socket-w-server';

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        /*
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;/**/
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = Factory::create();
        $pusher = new Pusher();

        $pdo = new \PDO('mysql:host=127.0.0.1;dbname=socket', 'root', '12345678');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $dbOptions = [
            'db_table'      => 'sessions',
            'db_id_col'     => 'sess_id',
            'db_data_col'   => 'sess_data',
            'db_time_col'   => 'sess_time',
            'db_lifetime_col' => 'sess_lifetime',
            'lock_mode'       => 0
        ];

        $context = new Context($loop);
        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
        $pull->bind('tcp://127.0.0.1:5555');
        $pull->on('message', [$pusher, 'onPushEventData']);
        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new Server('0.0.0.0:8081', $loop); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new IoServer(
            new HttpServer(
                new SessionProvider(
                    new WsServer(
                        new WampServer(
                            $pusher
                        )
                    ),
                    new PdoSessionHandler($pdo,$dbOptions)
                )
            ),
            $webSock
        );
        $loop->run();
    }
}
