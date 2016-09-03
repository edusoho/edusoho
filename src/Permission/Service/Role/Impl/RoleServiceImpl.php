<?php
namespace Permission\Service\Role\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Permission\Service\Role\RoleService;

class RoleServiceImpl extends BaseService implements RoleService
{
    public function getRole($id)
    {
        return $this->getRoleDao()->getRole($id);
    }

    public function getRoleByCode($code)
    {
        return $this->getRoleDao()->getRoleByCode($code);
    }

    public function findRolesByCodes($codes)
    {
        return $this->getRoleDao()->findRolesByCodes($codes);
    }

    public function createRole($role)
    {
        $role['createdTime']   = time();
        $user                  = $this->getCurrentUser();
        $role['createdUserId'] = $user['id'];
        $role                  = ArrayToolkit::parts($role, array('name', 'code', 'data', 'createdTime', 'createdUserId'));
        $this->getLogService()->info('role', 'create_role', '新增权限用户组"'.$role['name'].'"', $role);
        return $this->getRoleDao()->createRole($role);
    }

    public function updateRole($id, array $fields)
    {
        $user                  = $this->getCurrentUser();
        $fields                = ArrayToolkit::parts($fields, array('name', 'code', 'data'));
        $fields['updatedTime'] = time();
        $role                  = $this->getRoleDao()->updateRole($id, $fields);
        $this->getLogService()->info('role', 'update_role', '更新权限用户组"'.$role['name'].'"', $role);
        return $role;
    }

    public function deleteRole($id)
    {
        $role = $this->getRoleDao()->getRole($id);
        if (!empty($role)) {
            $this->getRoleDao()->deleteRole($id);
            $this->getLogService()->info('role', 'delete_role', '删除橘色"'.$role['name'].'"', $role);
        }
    }

    public function searchRoles($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        switch ($sort) {
            case 'created':
                $sort = array('createdTime', 'DESC');
                break;
            case 'createdByAsc':
                $sort = array('createdTime', 'ASC');
                break;

            default:
                throw $this->createServiceException('参数sort不正确。');
                break;
        }
        $roles = $this->getRoleDao()->searchRoles($conditions, $sort, $start, $limit);

        return $roles;
    }

    public function searchRolesCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);
        return $this->getRoleDao()->searchRolesCount($conditions);
    }

    public function initRoles()
    {
        $getSuperAdminRole = PermissionBuilder::instance()->getOriginPermissionTree();
        $getSuperAdminRole = ArrayToolkit::column($getSuperAdminRole, 'code');
        
        $getAdminRole = PermissionBuilder::instance()->findpersmissionsbycode('web');
        $getAdminRole = ArrayToolkit::column($getAdminRole, 'code');
        
        $getTeacherRole = PermissionBuilder::instance()->findpersmissionsbycode('web');
        $getTeacherRole = ArrayToolkit::column($getTeacherRole, 'code');

        $superAdminRole = $this->getRoleDao()->getRoleByCode('ROLE_SUPER_ADMIN');
        $adminRole      = $this->getRoleDao()->getRoleByCode('ROLE_ADMIN');
        $teacherRole    = $this->getRoleDao()->getRoleByCode('ROLE_TEACHER');
        $userRole       = $this->getRoleDao()->getRoleByCode('ROLE_USER');
        if (empty($superAdminRole)) {
            $superAdminRole = $this->makeRole('ROLE_SUPER_ADMIN', $getSuperAdminRole);
            $superAdminRole = $this->initCreateRole($superAdminRole);
        } else {
            $superAdminRole = $this->getRoleDao()->updateRole($superAdminRole['id'], $getSuperAdminRole);
        }
        if (empty($adminRole)) {
            $adminRole = $this->makeRole('ROLE_ADMIN', $getAdminRole);
            $adminRole = $this->initCreateRole($adminRole);
        } else {
            $adminRole = $this->getRoleDao()->updateRole($adminRole['id'], $getAdminRole);
        }
        if (empty($teacherRole)) {
            $teacherRole = $this->makeRole('ROLE_TEACHER', $getTeacherRole);
            $teacherRole = $this->initCreateRole($teacherRole);
        } else {
            $teacherRole = $this->getRoleDao()->updateRole($teacherRole['id'], $getTeacherRole);
        }
        if (empty($userRole)) {
            $userRole = $this->makeRole('ROLE_USER', array());
            $userRole = $this->initCreateRole($userRole);
        }
        return array($superAdminRole, $adminRole, $teacherRole, $userRole);
    }

    protected function makeRole($code, $role)
    {
        if ($code == 'ROLE_SUPER_ADMIN') {
            $condition = array(
                'name' => '超级管理员',
                'code' => 'ROLE_SUPER_ADMIN',
                'data' => $role
            );
            return $condition;
        }
        if ($code == 'ROLE_ADMIN') {
            $condition = array(
                'name' => '管理员',
                'code' => 'ROLE_ADMIN',
                'data' => $role
            );
            return $condition;
        }
        if ($code == 'ROLE_TEACHER') {
            $condition = array(
                'name' => '教师',
                'code' => 'ROLE_TEACHER',
                'data' => $role
            );
            return $condition;            
        }
        if ($code == 'ROLE_USER') {
            $condition = array(
                'name' => '学生',
                'code' => 'ROLE_USER',
                'data' => $role
            );
            return $condition;
        }
    }

    protected function initCreateRole($role)
    {
        $role['createdTime']   = time();
        $role['createdUserId'] = 1;
        $this->getLogService()->info('role', 'init_create_role', '初始化四个角色"'.$role['name'].'"', $role);
        return $this->getRoleDao()->createRole($role);        
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['nextExcutedStartTime']) && !empty($conditions['nextExcutedEndTime'])) {
            $conditions['nextExcutedStartTime'] = strtotime($conditions['nextExcutedStartTime']);
            $conditions['nextExcutedEndTime']   = strtotime($conditions['nextExcutedEndTime']);
        } else {
            unset($conditions['nextExcutedStartTime']);
            unset($conditions['nextExcutedEndTime']);
        }

        if (empty($conditions['cycle'])) {
            unset($conditions['cycle']);
        }

        if (empty($conditions['name'])) {
            unset($conditions['name']);
        } else {
            $conditions['nameLike'] = '%'.$conditions['name'].'%';
        }

        return $conditions;
    }

    public function isRoleNameAvalieable($name, $exclude = null)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $role = $this->getRoleDao()->getRoleByName($name);
        return $role ? false : true;
    }

    public function isRoleCodeAvalieable($code, $exclude = null)
    {
        // if (empty($code) || in_array($code, array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) {
        //     return false;
        // }

        if ($code == $exclude) {
            return true;
        }

        $tag = $this->getRoleByCode($code);

        return $tag ? false : true;
    }

    protected function getRoleDao()
    {
        return $this->createDao('Permission:Role.RoleDao');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
