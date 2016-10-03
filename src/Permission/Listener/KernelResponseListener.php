<?php
namespace Permission\Listener;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class KernelResponseListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

       /* $user = ServiceKernel::instance()->getCurrentUser()->toArray();
        $user =ArrayToolkit::parts($user, array('email','roles','password','salt','id'));

        ServiceKernel::instance()->getCurrentUser()->fromArray($user);*/
        ServiceKernel::instance()->getCurrentUser()->setPermissions(null);
    }
}
