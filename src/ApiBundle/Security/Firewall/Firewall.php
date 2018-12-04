<?php

namespace ApiBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Firewall implements ListenerInterface
{
    private $listeners;

    public function __construct(array $listeners)
    {
        $this->listeners = $listeners;
    }

    public function addListener($listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @return TokenInterface
     */
    public function handle(Request $request)
    {
        foreach ($this->listeners as $listener) {
            $listener->handle($request);
        }

        return null;
    }
}
