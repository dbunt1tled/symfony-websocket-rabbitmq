<?php

namespace App\Controller;

use App\Entity\User;
use App\Events\Messenger\UpdateClient;
use App\Services\Notifications\Notifications;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class IndexController extends AbstractController
{

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Notifications */
    private $notifications;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Notifications $notifications
    )
    {

        $this->tokenStorage = $tokenStorage;
        $this->notifications = $notifications;
    }

    /**
     * @Route("/index", name="index")
     */
    public function index(Request $request)
    {
        if ($this->tokenStorage->getToken()->getUser() instanceof UserInterface) {
            $request->getSession()->set(User::USER_SESSION_KEY, $this->tokenStorage->getToken()->getUser()->getId());
            $request->getSession()->set('nickname', $this->tokenStorage->getToken()->getUser()->getUsername());
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
    /**
     * @Route("/index1", name="index1")
     */
    public function index1()
    {
        return $this->render('index/index1.html.twig', [
            'controller_name' => 'Index1Controller',
        ]);
    }
    /**
     * @Route("/test", name="test")
     */
    public function test()
    {
        $entryData = [
            'test' => 'Yo',
            'date' => time(),
            'subscribeKey' => 'eventMonitoring',
        ];
        try {
            $this->notifications->send($entryData);
            return $this->json('YES', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }
    /**
     * @Route("/test1", name="test1")
     */
    public function test1()
    {
        $entryData = [
            'test' => 'Yo',
            'date' => time(),
            'subscribeKey' => 'eventMessage',
            'userID' => 2,
        ];
        try {
            $this->notifications->send($entryData);
            return $this->json('YES', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }
}
