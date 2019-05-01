<?php


namespace App\Events\Messenger;

use Ratchet\Wamp\WampServerInterface;
use Symfony\Component\EventDispatcher\Event;

class UpdateClient extends Event
{
    public const NAME = 'messenger.update.client';

    private $pusher;

    public function __construct($pusher)
    {
        $this->pusher = $pusher;
    }


    public function getPusher()
    {
        return $this->pusher;
    }

}
