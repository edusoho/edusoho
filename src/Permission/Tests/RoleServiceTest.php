<?php

namespace Permission\Service\Tests;

use Topxia\Service\Common\BaseTestCase;


class  RoleServiceTest extends BaseTestCase
{
    public function testInitRoles()
    {
        $superAdminRole = array(
            'name' => '超级管理员',
            'code' => 'ROLE_SUPER_ADMIN',
            'data' => $role
        );
        $adminRole = array(
            'name' => '管理员',
            'code' => 'ROLE_ADMIN',
            'data' => $role
        );
        $teacherRole = array(
            'name' => '教师',
            'code' => 'ROLE_TEACHER',
            'data' => $role
        );
        $userRole = array(
            'name' => '学生',
            'code' => 'ROLE_USER',
            'data' => $role
        );
        list($superAdminRole1, $adminRole1, $teacherRole1, $userRole1) = $this->getRoleService()->initRoles();
    }

    protected function getRoleService()
    {
        return $this->getServiceKernel()->createService('Permission:Role.RoleService');
    }
}