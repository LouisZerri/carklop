<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginSuccessListener
{
    public function __construct(
        private RequestStack $requestStack,
    ) {}

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        /** @var SessionInterface $session */
        $session = $this->requestStack->getSession();

        if ($session instanceof SessionInterface) {
            $session->getFlashBag()->add('success', 'Vous êtes connecté');
        }
    }
}
