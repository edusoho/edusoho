<?php
namespace Permission\Listener;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Common\AccessDeniedException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class KernelControllerListener
{
    protected $paths;

    public function __construct($container, $paths)
    {
        $this->container = $container;
        $this->paths     = $paths;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $currentUser = ServiceKernel::instance()->getCurrentUser();

        $request     = $event->getRequest();
        $route       = $request->attributes->get('_route');
        $permissions = $this->container
            ->get('router')
            ->getRouteCollection()
            ->get($route)
            ->getPermissions();

        $requestPath = $request->getPathInfo();

        foreach ($this->paths as $key => $path) {
            if (preg_match($path, $requestPath)
                && !empty($permissions)
                && !in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])) {
                $currentUserPermissions = $currentUser->getPermissions();

                foreach ($permissions as $permission) {
                    if (!empty($currentUserPermissions[$permission])) {
                        return;
                    }
                }
                throw new AccessDeniedException('无权限访问！');
            }
        }
    }
}
