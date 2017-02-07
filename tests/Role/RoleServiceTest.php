<?php

namespace Tests\Role;

use Biz\Role\Service\RoleService;
use Topxia\Common\Tree;
use Topxia\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\Role\Util\PermissionBuilder;

class  RoleServiceTest extends BaseTestCase
{
    public function testrefreshRoles()
    {
        $permissions = PermissionBuilder::instance()->getOriginPermissions();
        $permissionTree = Tree::buildWithArray($permissions, null, 'code', 'parent');
        $superAdminPermissions = ArrayToolkit::column($permissions, 'code');


        $adminForbiddenRootPermissions   = array(
            'admin_user_avatar',
            'admin_user_change_password',
            'admin_my_cloud',
            'admin_cloud_video_setting',
            'admin_edu_cloud_sms',
            'admin_edu_cloud_search_setting',
            'admin_setting_cloud_attachment',
            'admin_setting_cloud',
            'admin_system'
        );

        $adminForbiddenPermissions = array();
        foreach ($adminForbiddenRootPermissions as $rootPermission) {
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

    public function testSearch()
    {
        $role1 = array(
            'name' => '管123理员',
            'code' => 'ADMIN',
            'createdUserId' => 1,
            'data' => array()
        );

        $role2 = array(
            'name' => '用123户',
            'code' => 'USER',
            'createdUserId' => 1,
            'data' => array()
        );

        $this->getRoleDao()->create($role1);
        $this->getRoleDao()->create($role2);

        $result1 = $this->getRoleService()->searchRoles(array('nameLike' => '管12'), array(), 0, 100);
        $this->assertCount(1, $result1);

        $result2 = $this->getRoleService()->searchRoles(array('nameLike' => '用12'), array(), 0, 100);
        $this->assertCount(1, $result2);
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    protected function getRoleDao()
    {
        return $this->createDao('Role:RoleDao');
    }
}