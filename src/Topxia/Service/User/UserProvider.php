<?php
namespace Topxia\Service\User;

use Topxia\Service\User\CurrentUser;
use Permission\Common\PermissionBuilder;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

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
        $user['org']       = $this->getOrgService()->getOrgByOrgCode($user['orgCode']);
        $currentUser       = new CurrentUser();
        $permissions       = $this->loadPermissions($user);

        $currentUser->setPermissions($permissions);
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

    protected function getOrgService()
    {
        return ServiceKernel::instance()->createService('Org:Org.OrgService');
    }
}
