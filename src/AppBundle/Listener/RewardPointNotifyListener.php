<?php

namespace AppBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RewardPointNotifyListener extends AbstractSecurityDisabledListener
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $saveNotifyToHeader = $request->isXmlHttpRequest() || 0 === strpos( $request->getPathInfo(),'/mapi_v2');
        $this->container->get('reward_point.response_decorator')->decorate($event->getResponse(), $saveNotifyToHeader);
    }
}
