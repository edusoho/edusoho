<?php
namespace Permission\Listener;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Common\AccessDeniedException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Permission\Common\PermissionBuilder;

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

        $currentUser = $this->getUserService()->getCurrentUser();
        $user = $this->getUserService()->getUser($currentUser['id']);
        $permissions = $this->loadPermissions($user);
        $currentUser->setPermissions($permissions);

        $request     = $event->getRequest();
        $route       = $request->attributes->get('_route');
        $permissions = $this->container->get('router')->getRouteCollection()->get($route)->getPermissions();
        if (preg_match('/admin/s', $route) && !empty($permissions) && !in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])) {
            $currentUserPermissions = $currentUser->getPermissions();

            foreach ($permissions as $permission) {
                if (!empty($currentUserPermissions[$permission])) {
                    return;
                }
            }

            throw new AccessDeniedException('无权限访问！');
        }
    }


    protected function loadPermissions($user)
    {
        if (empty($user['id'])) {
            return $user;
        }

        $permissionBuilder = new PermissionBuilder();
        $originPermissions = $permissionBuilder->getOriginPermissions();
        if (in_array('ROLE_SUPER_ADMIN', $user['roles'])) {
            return $originPermissions;
        }

        $permissionCode = array();
        foreach ($user['roles'] as $code) {
            $role = $this->getRoleService()->getRoleByCode($code);

            if (empty($role['data'])) {
                $role['data'] = array();
            }

            $permissionCode = array_merge($permissionCode, $role['data']);
        }

        $permissions = array();
        foreach ($originPermissions as $key => $value) {
            if (in_array($key, $permissionCode)) {
                $permissions[$key] = $originPermissions[$key];
            }
        }

        return $permissions;
    }

    protected function getRoleService()
    {
        return ServiceKernel::instance()->createService('Permission:Role.RoleService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
