<?php
namespace Topxia\WebBundle\Listener;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\Yaml\Yaml;
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
        $currentUser = $this->getUserService()->getCurrentUser();
        $path        = realpath(__DIR__.'/../..')."/AdminBundle/Resources/config/menus_admin.yml";
        $menus       = Yaml::parse($path);

        if (empty($menus[$route]) && !in_array($route, ArrayToolkit::column($menus, 'router_name'))) {
            return;
        }

        if (preg_match('/admin/s', $route)) {
            if (empty($currentUser['menus'][$route]) && !in_array($route, ArrayToolkit::column($currentUser['menus'], 'router_name'))) {
                throw new AccessDeniedException('无权限访问！');
            }
        }
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
