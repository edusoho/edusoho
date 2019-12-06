<?php

namespace Tests\Unit\Role\Service;

use Biz\Role\Service\RoleService;
use AppBundle\Common\Tree;
use Biz\BaseTestCase;
use Biz\Role\Util\PermissionBuilder;
use AppBundle\Common\ReflectionUtils;
use Biz\System\Service\SettingService;

class RoleServiceTest extends BaseTestCase
{
    public function testrefreshRoles()
    {
        $this->getRoleService()->refreshRoles();
        $permissions = PermissionBuilder::instance()->loadPermissionsFromAllConfig();
        $tree = Tree::buildWithArray($permissions, null, 'code', 'parent');
        $getAdminRoles = $tree->find(function ($tree) {
            return 'admin' === $tree->data['code'];
        });
        $adminRoles = $getAdminRoles->column('code');
        $getWebRoles = $tree->find(function ($tree) {
            return 'web' === $tree->data['code'];
        });
        $webRoles = $getWebRoles->column('code');

        $superAdminRole = $this->getRoleService()->getRoleByCode('ROLE_SUPER_ADMIN');
        $this->assertEquals(count(array_merge($adminRoles, $webRoles)), count($superAdminRole['data']));

        $teacherRole = $this->getRoleService()->getRoleByCode('ROLE_TEACHER');
        $this->assertEquals(count($webRoles), count($teacherRole['data']));

        $userRole = $this->getRoleService()->getRoleByCode('ROLE_USER');
        $this->assertEquals(count(array()), count($userRole['data']));
    }

    public function testSearch()
    {
        $role1 = array(
            'name' => '管123理员',
            'code' => 'ADMIN',
            'createdUserId' => 1,
            'data' => array(),
        );

        $role2 = array(
            'name' => '用123户',
            'code' => 'USER',
            'createdUserId' => 1,
            'data' => array(),
        );

        $this->getRoleDao()->create($role1);
        $this->getRoleDao()->create($role2);

        $result1 = $this->getRoleService()->searchRoles(array('nameLike' => '管12'), array(), 0, 100);
        $this->assertCount(1, $result1);

        $result2 = $this->getRoleService()->searchRoles(array('nameLike' => '用12'), array(), 0, 100);
        $this->assertCount(1, $result2);
    }

    public function testGetRole()
    {
        $this->mockBiz(
            'Role:RoleDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'role1'),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getRoleService()->getRole(1);
        $this->assertEquals(array('id' => 1, 'title' => 'role1'), $result);
    }

    public function testCreateRole()
    {
        $role = array('name' => 'test', 'code' => 'ROLE_TEST', 'data' => '');
        $result = $this->getRoleService()->createRole($role);
        $this->assertEquals('test', $result['name']);
    }

    public function testUpdateRole()
    {
        $role = array('name' => 'test', 'code' => 'ROLE_TEST', 'data' => '');
        $role = $this->getRoleService()->createRole($role);
        $result = $this->getRoleService()->updateRole($role['id'], array('data' => 'test', 'code' => 'ROLE_TEST'));
        $this->assertEquals('test', $result['data']);
    }

    public function testDeleteRole()
    {
        $role = array('name' => 'test', 'code' => 'ROLE_TEST', 'data' => '');
        $role = $this->getRoleService()->createRole($role);
        $this->getRoleService()->deleteRole($role['id']);
        $result = $this->getRoleService()->getRole($role['id']);
        $this->assertNull($result);
    }

    public function testSearchRoles()
    {
        $role = array('name' => 'test', 'code' => 'ROLE_TEST', 'data' => '');
        $role = $this->getRoleService()->createRole($role);
        $result = $this->getRoleService()->searchRoles(array('nameLike' => 'test'), 'createdByAsc', 0, 10);
        $this->assertEquals($role, $result[0]);
    }

    public function testSearchRolesCount()
    {
        $result = $this->getRoleService()->searchRolesCount(array('nextExcutedStartTime' => 0, 'nextExcutedEndTime' => 0));
        $this->assertEquals(4, $result);
    }

    public function testFindRolesByCodes()
    {
        $result = $this->getRoleService()->findRolesByCodes(array());
        $this->assertEquals(array(), $result);
    }

    /**
     * @expectedException \Biz\Role\RoleException
     */
    public function testCheckChangeRole()
    {
        $service = $this->getRoleService();
        $result = ReflectionUtils::invokeMethod($service, 'checkChangeRole', array(1));
    }

    public function testIsRoleNameAvalieable()
    {
        $result = $this->getRoleService()->isRoleNameAvalieable('');
        $this->assertFalse($result);

        $result = $this->getRoleService()->isRoleNameAvalieable('test', 'test');
        $this->assertTrue($result);

        $result = $this->getRoleService()->isRoleNameAvalieable('学员');
        $this->assertFalse($result);
    }

    public function testIsRoleCodeAvalieable()
    {
        $result = $this->getRoleService()->isRoleCodeAvalieable('test', 'test');
        $this->assertTrue($result);

        $result = $this->getRoleService()->isRoleCodeAvalieable('ROLE_USER');
        $this->assertFalse($result);
    }

    public function testRolesTreeTrans()
    {
        $tree = PermissionBuilder::instance()->getOriginPermissionTree();
        $res = $tree->toArray();
        $children = $this->getRoleService()->rolesTreeTrans($res['children']);
        $this->assertTrue(in_array('admin', $children[0]) || in_array('admin_v2', $children[0]));
    }

    public function testSplitRolesTreeNode()
    {
        $tree = PermissionBuilder::instance()->getOriginPermissionTree();
        $res = $tree->toArray();
        $nodes = $this->getRoleService()->splitRolesTreeNode($res['children']);
        $this->assertTrue(!empty($nodes['admin_v2']));
    }

    public function testGetParentRoleCodeArray()
    {
        $tree = PermissionBuilder::instance()->getOriginPermissionTree();
        $res = $tree->toArray();
        $nodes = $this->getRoleService()->splitRolesTreeNode($res['children']);

        $permissions = $this->getRoleService()->getParentRoleCodeArray('admin_v2_course_show', $nodes);
        $this->assertTrue(in_array('admin_v2', $permissions));
    }

    public function testGetAllParentPermissions()
    {
        $result = $this->getRoleService()->getAllParentPermissions(array('admin_v2_course_show'));
        $this->assertTrue(in_array('admin_v2', $result));
    }

    public function testFilterRoleTree()
    {
        $this->getSettingService()->set('backstage', array('is_v2' => 1));
        $tree = PermissionBuilder::instance()->getOriginPermissionTree();
        $res = $tree->toArray();
        $children = $this->getRoleService()->rolesTreeTrans($res['children']);
        $result = $this->getRoleService()->filterRoleTree($children);
        $this->assertEquals('admin_v2', $result[0]['code']);
    }

    public function testGetPermissionsYmlContent()
    {
        $result = $this->getRoleService()->getPermissionsYmlContent();
        $this->assertEquals(array('admin_v2'), $result['admin']['admin']);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
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
