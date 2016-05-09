<?php
namespace Topxia\Service\User;

use Symfony\Component\Yaml\Yaml;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Topxia\Common\MenuBuilder;

class UserProvider implements UserProviderInterface
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->getUserService()->getUserByLoginField($username);

        if (empty($user)) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        $user['currentIp'] = $this->container->get('request')->getClientIp();
        $currentUser       = new CurrentUser();
        $currentUser->setPermissions($this->loadPermissions($user));
        $currentUser->fromArray($user);
        ServiceKernel::instance()->setCurrentUser($currentUser);

        return $currentUser;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof CurrentUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Topxia\Service\User\CurrentUser';
    }

    protected function loadPermissions($user)
    {
        if (empty($user['id'])) {
            return $user;
        }

        $menuBuilder = new MenuBuilder('admin');
        $configs = $menuBuilder->getMenusYml();

        $res = array();
        foreach ($configs as $key => $config) {
            if(!file_exists($config)) {
                continue;
            }
            $menus = Yaml::parse(file_get_contents($config));
            if(empty($menus)) {
                continue;
            }

            $menus = $this->getMenusFromConfig($menus);
            $res = array_merge($res, $menus);
        }

        if (in_array('ROLE_SUPER_ADMIN', $user['roles'])) {
            return $res;
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
        foreach ($res as $key => $value) {
            if (in_array($key, $permissionCode)) {
                $permissions[$key] = $res[$key];
            }
        }

        return $permissions;
    }

    protected function getMenusFromConfig($parents)
    {
        $menus = array();
        $key = key($parents);

        if(isset($parents[$key]['children'])) {
            $childrenMenu = $parents[$key]['children'];
            unset($parents[$key]['children']);
            foreach ($childrenMenu as $childKey => $childValue) {
                $childValue["parent"] = $key;
                $menus = array_merge($menus, $this->getMenusFromConfig(array($childKey => $childValue)));
            }
        }

        $menus[$key] = $parents[$key];

        return $menus;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getRoleService()
    {
        return ServiceKernel::instance()->createService('System.RoleService');
    }
}
