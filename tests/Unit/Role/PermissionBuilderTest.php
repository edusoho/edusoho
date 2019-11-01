<?php

namespace Tests\Unit\Tests;

use Biz\BaseTestCase;
use Biz\CloudPlatform\Service\AppService;
use Biz\Role\Util\PermissionBuilder;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class PermissionBuilderTest extends BaseTestCase
{
    public function testGetPermissionConfig()
    {
        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $expected = array(
            $rootDir.'/../src/AppBundle/Resources/config/menus_admin.yml',
            $rootDir.'/../src/AppBundle/Resources/config/menus_admin_v2.yml',
        );

        $this->assertEquals($expected, PermissionBuilder::instance()->getPermissionConfig());
    }

    public function testLoadPermissionsFromAllConfig()
    {
        $builder = PermissionBuilder::instance();

        $result = $builder->loadPermissionsFromAllConfig();
        $this->assertArrayHasKey('admin', $result);
    }

    public function testGetOriginPermissions()
    {
        $builder = PermissionBuilder::instance();
        $expected = $builder->loadPermissionsFromAllConfig();

        $this->assertEquals($expected, $builder->getOriginPermissions());
    }

    public function testGetPermissionsByRolesEmpty()
    {
        $this->assertEmpty(PermissionBuilder::instance()->getPermissionsByRoles(array()));
    }

    public function testGetPermissionsByRolesWithRoleSuperAdmin()
    {
        $builder = PermissionBuilder::instance();
        $expected = $builder->getOriginPermissions();

        $result = $builder->getPermissionsByRoles(array('ROLE_SUPER_ADMIN'));
        $this->assertEquals($expected, $result);
    }

    public function testGetPermissionsByRolesWithRoleAdmin()
    {
        $result = PermissionBuilder::instance()->getPermissionsByRoles(array('ROLE_ADMIN'));
        $this->assertArrayHasKey('admin', $result);
        $this->assertArrayNotHasKey('admin_system', $result);

        $result = PermissionBuilder::instance()->getPermissionsByRoles(array('ROLE_USER'));
        $this->assertEmpty($result);
    }

    public function testGetUserPermissionTree()
    {
        $tree = PermissionBuilder::instance()->getUserPermissionTree();

        $this->assertInstanceOf('AppBundle\Common\Tree', $tree);
        $this->assertNotEmpty($tree);
    }

    public function testGetOriginSubPermissions()
    {
        $result = PermissionBuilder::instance()->getOriginSubPermissions('admin');
        $this->assertCount(9, $result);
    }

    public function testGetSubPermissions()
    {
        $this->assertEmpty(PermissionBuilder::instance()->getSubPermissions('test_code', null));
        $this->assertCount(9, PermissionBuilder::instance()->getSubPermissions('admin', null));
    }

    public function testGroupedPermissions()
    {
        $this->assertEmpty(PermissionBuilder::instance()->groupedPermissions('test_code'));
        $result = PermissionBuilder::instance()->groupedPermissions('admin');
        $result = array_values($result);
        $this->assertCount(1, $result);
        $this->assertCount(9, $result[0]);
    }

    public function testgetPermissionByCode()
    {
        $user = $this->getCurrentUser();
        $permissions = $this->loadPermissions($user->toArray());
        $user->setPermissions($permissions);

        $permissionBuilder = PermissionBuilder::instance();
        $result = $permissionBuilder->getPermissionByCode('admin_user_show');

        $this->assertArrayHasKey('parent', $result);
        $this->assertEquals('admin_user', $result['parent']);
        $this->assertEmpty($permissionBuilder->getPermissionByCode('test_code'));
    }

    public function testGetOriginPermissionTree()
    {
        $result = PermissionBuilder::instance()->getOriginPermissionTree(false);

        $this->assertInstanceOf('AppBundle\Common\Tree', $result);
    }

    public function testGetOriginPermissionByCode()
    {
        $this->assertNotEmpty(PermissionBuilder::instance()->getOriginPermissionByCode('admin_user'));
        $this->assertEmpty(PermissionBuilder::instance()->getOriginPermissionByCode('admin_test_permission'));
    }

    public function testGetParentPermissionByCode()
    {
        $this->assertNotEmpty(PermissionBuilder::instance()->getParentPermissionByCode('admin_user_show'));
        $this->assertEmpty(PermissionBuilder::instance()->getParentPermissionByCode('admin_test_permission'));
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

    /**
     * @return AppService
     */
    private function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }
}
