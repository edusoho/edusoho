<?php

namespace Tests\Role;

use Topxia\Common\Tree;
use Topxia\Common\ArrayToolkit;
use Biz\BaseTestCase;;
use Permission\Common\PermissionBuilder;


class  RoleServiceTest extends BaseTestCase
{
    public function testrefreshRoles()
    {
        $getAllRole = PermissionBuilder::instance()->getOriginPermissions();
        $permissionTree = Tree::buildWithArray($getAllRole, null, 'code', 'parent');
        $getSuperAdminRole = ArrayToolkit::column($getAllRole, 'code');

        $adminRootRoles = array('admin_user_avatar', 'admin_user_change_password','admin_my_cloud', 'admin_cloud_video_setting', 'admin_edu_cloud_sms', 'admin_edu_cloud_search_setting', 'admin_setting_cloud_attachment', 'admin_setting_cloud', 'admin_system');

        $getAdminRole = array();
        foreach ($adminRootRoles as $adminRootRole) {
            $adminRole = $permissionTree->find(function ($tree) use ($adminRootRole){
                return $tree->data['code'] === $adminRootRole;
            });
            $getAdminRole = array_merge($adminRole->column('code'), $getAdminRole);
        }
        $getAdminRole = array_diff($getSuperAdminRole, $getAdminRole);

        $getTeacherRole = $permissionTree->find(function ($tree){
            return $tree->data['code'] === 'web';
        });
        $getUserRole = array();

        $userRoles = $this->getRoleService()->refreshRoles();
        $role = $this->getRoleService()->getRoleByCode('ROLE_SUPER_ADMIN');
        $this->assertEquals(count($getSuperAdminRole), count($role['data']));

        $role = $this->getRoleService()->getRoleByCode('ROLE_ADMIN');
        $this->assertEquals(count($getAdminRole), count($role['data']));

        $role = $this->getRoleService()->getRoleByCode('ROLE_TEACHER');
        $this->assertEquals(count($getTeacherRole->column('code')), count($role['data']));
        
        $role = $this->getRoleService()->getRoleByCode('ROLE_USER');
        $this->assertEquals(count($getUserRole), count($role['data']));
    }

    protected function getRoleService()
    {
        return $this->getServiceKernel()->createService('Role:RoleService');
    }
}