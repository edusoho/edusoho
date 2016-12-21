<?php

namespace Tests\Role;

use Biz\Role\Service\RoleService;
use Topxia\Common\Tree;
use Topxia\Common\ArrayToolkit;
use Biz\BaseTestCase;;
use Permission\Common\PermissionBuilder;


class  RoleServiceTest extends BaseTestCase
{
    public function testrefreshRoles()
    {
        $permissions = PermissionBuilder::instance()->getOriginPermissions();
        $permissionTree = Tree::buildWithArray($permissions, null, 'code', 'parent');
        $superAdminPermissions = ArrayToolkit::column($permissions, 'code');

        $adminForbiddenPermissions = array();

        foreach (RoleService::ADMIN_FORBIDDEN_PERMISSIONS as $rootPermission) {
            $adminRoleTree = $permissionTree->find(function ($permissionNode) use ($rootPermission){
                return $permissionNode->data['code'] === $rootPermission;
            });
            $adminForbiddenPermissions = array_merge($adminRoleTree->column('code'), $adminForbiddenPermissions);
        }

        $adminPermissions = array_diff($superAdminPermissions, $adminForbiddenPermissions);

        $teacherRoleTree = $permissionTree->find(function ($tree){
            return $tree->data['code'] === 'web';
        });

        $this->getRoleService()->refreshRoles();

        $superAdminRole = $this->getRoleService()->getRoleByCode('ROLE_SUPER_ADMIN');
        $this->assertEquals(count($superAdminPermissions), count($superAdminRole['data']));

        $adminRole = $this->getRoleService()->getRoleByCode('ROLE_ADMIN');
        $this->assertEquals(count($adminPermissions), count($adminRole['data']));

        $teacherRole = $this->getRoleService()->getRoleByCode('ROLE_TEACHER');
        $this->assertEquals(count($teacherRoleTree->column('code')), count($teacherRole['data']));

        $userRole = $this->getRoleService()->getRoleByCode('ROLE_USER');
        $this->assertEquals(count(array()), count($userRole['data']));
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->getServiceKernel()->createService('Role:RoleService');
    }
}