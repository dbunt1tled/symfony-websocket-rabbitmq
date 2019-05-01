<?php


namespace App\Components\Messenger;


use App\Entity\User;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\MessageComponentInterface;

class Pusher implements WampServerInterface, MessageComponentInterface
{
    protected $subscribedTopics = [];
    /** @var array */
    protected $clients;

    public function __construct()
    {
        $this->clients = [];
    }

    function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv === 1 ? '' : 's');
        $from->send('Boroda');
        /*
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }/**/
    }

    function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $subject = $topic->getId();
        if (!array_key_exists($subject, $this->subscribedTopics))
        {
            $this->subscribedTopics[$subject] = $topic;
        }
    }

    public function onPushEventData($event)
    {
        $eventData = json_decode($event, true);

        //Здесь в массиве $eventData мы тоже передаём идентификатор и проверяем есть ли подписанные клиенты.
        if (!array_key_exists($eventData['subscribeKey'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$eventData['subscribeKey']];
        if (!($topic instanceof Topic)) {
            return;
        }
        switch ($eventData['subscribeKey']) {
            case 'eventMonitoring':
                $this->escape($eventData);
                $topic->broadcast($eventData);
                break;
            case 'eventMessage':
                //$this->escape($eventData);
                if (!isset($eventData['userID']) || $eventData['userID'] < 1) {
                    break;
                }
                $this->sendClient($topic, $eventData['userID'], $eventData);
                break;
        }
    }

    function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onUnSubscribe() method.
    }

    function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $conn->close();
    }

    function onOpen(ConnectionInterface $conn)
    {
        if (isset($conn->resourceId) && $conn->Session->get(User::USER_SESSION_KEY)) {
            $this->clients[$conn->Session->get(User::USER_SESSION_KEY)][$conn->resourceId] = $conn;
            return;
        }
    }

    function onClose(ConnectionInterface $conn)
    {
        if (isset($conn->resourceId) && $conn->Session->get(User::USER_SESSION_KEY)) {
            unset($this->clients[$conn->Session->get(User::USER_SESSION_KEY)][$conn->resourceId]);
        }
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }
    private function escape(&$data)
    {
        if (is_iterable($data)) {
            foreach ($data as $eventField => &$fieldValue) {
                $fieldValue = htmlspecialchars($fieldValue, ENT_QUOTES);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES);
        }
    }
    public function sendClient(Topic $topic, $userId, $msg) {
        if (!isset($this->clients[$userId]) || !$this->clients[$userId]) {
            return $topic;
        }
        $clients = $this->clients[$userId];
        foreach ($clients as $client) {
            if ($topic->has($client)) {
                $client->event($topic->getId(), $msg);
            }
        }
        return $topic;
    }
}
