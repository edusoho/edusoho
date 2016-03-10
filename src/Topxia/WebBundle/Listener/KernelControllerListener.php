<?php
namespace Topxia\WebBundle\Listener;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Common\AccessDeniedException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class KernelControllerListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request     = $event->getRequest();
        $route       = $request->attributes->get('_route');
        $permissions = $this->container->get('router')->getRouteCollection()->get($route)->getPermissions();

        $currentUser = $this->getUserService()->getCurrentUser();

        if (preg_match('/admin/s', $route) && !in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])) {
            foreach ($permissions as $permission) {
                if (isset($currentUser['menus'][$permission])) {
                    return;
                }
            }

            throw new AccessDeniedException('无权限访问！');
        }
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
