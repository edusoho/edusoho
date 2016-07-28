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

        $tag = $this->getRoleDao()->getRoleByName($name);

        return $tag ? false : true;
    }

    public function isRoleCodeAvalieable($code, $exclude = null)
    {
        if (empty($code) || in_array($code, array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) {
            return false;
        }

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
}
