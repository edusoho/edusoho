<?php
namespace Topxia\Service\User;

use Symfony\Component\Yaml\Yaml;
use Topxia\Service\User\CurrentUser;
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
        $currentUser       = new CurrentUser();
        $user              = $this->setPermissions($user);
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

    private function setPermissions($user)
    {
        if (empty($user['id'])) {
            return $user;
        }

        $menus = array();
        $codes = array();
        $path  = realpath(__DIR__.'/../..')."/AdminBundle/Resources/config/menus_admin.yml";
        $res   = Yaml::parse($path);

        if (in_array('ROLE_SUPER_ADMIN', $user['roles'])) {
            $menus = $res;
        }

        foreach ($user['roles'] as $code) {
            $role = $this->getRoleService()->getRoleByCode($code);

            if (empty($role['data'])) {
                $role['data'] = array();
            }

            $codes = array_merge($codes, $role['data']);
        }

        foreach ($res as $key => $value) {
            if (in_array($key, $codes)) {
                $menus[$key] = $res[$key];
            }
        }

        $user['menus'] = $menus;
        return $user;
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
