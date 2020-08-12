<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Email\Mailer;
use App\Entity\User;
use App\Security\TokenGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserRegisterSubscriber implements EventSubscriberInterface
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private TokenGenerator $tokenGenerator;
    private Mailer $mailer;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, TokenGenerator $tokenGenerator,
        Mailer $mailer)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['userRegistered', EventPriorities::PRE_WRITE]
        ];
    }

    public function userRegistered(ViewEvent $event)
    {
        /** @var User */
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User ||
            !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
        $user->setConfirmationToken($this->tokenGenerator->getRandomSecureToken());

        $this->mailer->sendConfirmationEmail($user);
    }
}