<?php

namespace Tests\Unit\Tests;

use Symfony\Component\Yaml\Yaml;
use Biz\BaseTestCase;
use Biz\Role\Util\PermissionBuilder;

class PermissionBuilderTest extends BaseTestCase
{
    public function testgetPermissionByCode()
    {
        $user = $this->getCurrentUser();
        $permissions = $this->loadPermissions($user->toArray());
        $user->setPermissions($permissions);

        $permissionBuilder = PermissionBuilder::instance();
        $permissionBuilder->getPermissionByCode('admin_user_show');
    }

    protected function loadPermissions($user)
    {
        if (empty($user['id'])) {
            return $user;
        }

        $permissionBuilder = PermissionBuilder::instance();
        $configs = $permissionBuilder->getPermissionConfig();

        $res = array();
        foreach ($configs as $key => $config) {
            if (!file_exists($config)) {
                continue;
            }
            $menus = Yaml::parse(file_get_contents($config));
            if (empty($menus)) {
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

        foreach ($parents as $key => $value) {
            if (isset($parents[$key]['children'])) {
                $childrenMenu = $parents[$key]['children'];
                unset($parents[$key]['children']);

                foreach ($childrenMenu as $childKey => $childValue) {
                    $childValue['parent'] = $key;
                    $menus = array_merge($menus, $this->getMenusFromConfig(array($childKey => $childValue)));
                }
            }

            $menus[$key] = $value;
        }

        return $menus;
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getRoleService()
    {
        return $this->createService('System:RoleService');
    }
}
