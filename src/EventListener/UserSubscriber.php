<?php


namespace App\EventListener;


use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

class UserSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $token;
    /** @var UserInterface */
    private $user;
    /** @var Security */
    private $security;

    public function __construct(TokenStorageInterface $tokenStorage, Security $security)
    {
        $this->token = $tokenStorage;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                'onKernelRequest',
                60,
            ],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {


    }
}
