<?php

namespace Biz\Role\Service\Impl;

use AppBundle\Common\PluginVersionToolkit;
use Biz\BaseService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Common\CommonException;
use Biz\Role\RoleException;
use Biz\Role\Service\RoleService;
use Biz\Role\Util\PermissionBuilder;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Tree;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class RoleServiceImpl extends BaseService implements RoleService
{
    public function getRole($id)
    {
        return $this->getRoleDao()->get($id);
    }

    public function getRoleByCode($code)
    {
        return $this->getRoleDao()->getByCode($code);
    }

    public function createRole($role)
    {
        $role['createdTime'] = time();
        $user = $this->getCurrentUser();
        $role['createdUserId'] = $user['id'];
        $role = ArrayToolkit::parts($role, array('name', 'code', 'data', 'data_v2', 'createdTime', 'createdUserId'));

        if (!ArrayToolkit::requireds($role, array('name', 'code'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!preg_match('/(^(?![^0-9a-zA-Z]+$))(?![0-9]+$).+/', $role['code'])) {
            $this->createNewException(RoleException::CODE_NOT_ALLL_DIGITAL());
        }

        return $this->getRoleDao()->create($role);
    }

    public function updateRole($id, array $fields)
    {
        $this->checkChangeRole($id);
        $fields = ArrayToolkit::parts($fields, array('name', 'code', 'data', 'data_v2'));

        if (isset($fields['code'])) {
            unset($fields['code']);
        }

        $fields['updatedTime'] = time();
        $role = $this->getRoleDao()->update($id, $fields);

        return $role;
    }

    public function deleteRole($id)
    {
        $role = $this->checkChangeRole($id);
        if (!empty($role)) {
            $this->getRoleDao()->delete($id);
        }
    }

    public function searchRoles($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        switch ($sort) {
            case 'created':
                $sort = array('createdTime' => 'DESC');
                break;
            case 'createdByAsc':
                $sort = array('createdTime' => 'ASC');
                break;

            default:
                $sort = array('createdTime' => 'DESC');
                break;
        }

        return $this->getRoleDao()->search($conditions, $sort, $start, $limit);
    }

    public function searchRolesCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getRoleDao()->count($conditions);
    }

    public function findRolesByCodes(array $codes)
    {
        if (empty($codes)) {
            return array();
        }

        return $this->getRoleDao()->findByCodes($codes);
    }

    public function refreshRoles()
    {
        $permissions = PermissionBuilder::instance()->loadPermissionsFromAllConfig();
        $tree = Tree::buildWithArray($permissions, null, 'code', 'parent');

        $getSuperAdminRoles = $tree->column('code');
        $adminForbidRoles = array(
            'admin_user_avatar',
            'admin_user_change_password',
            'admin_my_cloud',
            'admin_cloud_video_setting',
            'admin_edu_cloud_sms',
            'admin_edu_cloud_search_setting',
            'admin_setting_cloud_attachment',
            'admin_setting_cloud',
            'admin_system',
        );

        $getAdminForbidRoles = array();
        foreach ($adminForbidRoles as $adminForbidRole) {
            $adminRole = $tree->find(function ($tree) use ($adminForbidRole) {
                return $tree->data['code'] === $adminForbidRole;
            });

            if (is_null($adminRole)) {
                continue;
            }

            $getAdminForbidRoles = array_merge($adminRole->column('code'), $getAdminForbidRoles);
        }

        $getTeacherRoles = $tree->find(function ($tree) {
            return 'web' === $tree->data['code'];
        });
        $getTeacherRoles = $getTeacherRoles->column('code');

        $roles = array(
            'ROLE_USER' => array(),
            'ROLE_TEACHER' => $getTeacherRoles,
            'ROLE_ADMIN' => array_diff($getSuperAdminRoles, $getAdminForbidRoles),
            'ROLE_SUPER_ADMIN' => $getSuperAdminRoles,
        );

        foreach ($roles as $key => $value) {
            $userRole = $this->getRoleDao()->getByCode($key);

            if (empty($userRole)) {
                $this->initCreateRole($key, array_values($value));
            } else {
                $this->getRoleDao()->update($userRole['id'], array('data' => array_values($value)));
            }
        }
    }

    private function initCreateRole($code, $role)
    {
        $userRoles = array(
            'ROLE_SUPER_ADMIN' => array('name' => '超级管理员', 'code' => 'ROLE_SUPER_ADMIN'),
            'ROLE_ADMIN' => array('name' => '管理员', 'code' => 'ROLE_ADMIN'),
            'ROLE_TEACHER' => array('name' => '教师', 'code' => 'ROLE_TEACHER'),
            'ROLE_USER' => array('name' => '学员', 'code' => 'ROLE_USER'),
        );
        $userRole = $userRoles[$code];

        $userRole['data'] = $role;
        $userRole['createdTime'] = time();
        $userRole['createdUserId'] = $this->getCurrentUser()->getId();
        $this->getLogService()->info('role', 'init_create_role', '初始化四个角色"'.$userRole['name'].'"', $userRole);

        return $this->getRoleDao()->create($userRole);
    }

    private function checkChangeRole($id)
    {
        $role = $this->getRoleDao()->get($id);
        $notUpdateRoles = array('ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_TEACHER', 'ROLE_USER');
        if (in_array($role['code'], $notUpdateRoles)) {
            $this->createNewException(RoleException::FORBIDDEN_MODIFY());
        }

        return $role;
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['nextExcutedStartTime']) && !empty($conditions['nextExcutedEndTime'])) {
            $conditions['nextExcutedStartTime'] = strtotime($conditions['nextExcutedStartTime']);
            $conditions['nextExcutedEndTime'] = strtotime($conditions['nextExcutedEndTime']);
        } else {
            unset($conditions['nextExcutedStartTime']);
            unset($conditions['nextExcutedEndTime']);
        }

        if (empty($conditions['cycle'])) {
            unset($conditions['cycle']);
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

        $role = $this->getRoleDao()->getByName($name);

        return $role ? false : true;
    }

    public function isRoleCodeAvalieable($code, $exclude = null)
    {
        if ($code == $exclude) {
            return true;
        }

        $tag = $this->getRoleByCode($code);

        return $tag ? false : true;
    }

    /**
     * @param $tree  '后台menus树结构数组'
     * @param $type  '新老后台类型 admin|adminV2'
     *
     * @return array
     *               将权限树里的国际化key进行转译，
     */
    public function rolesTreeTrans($tree, $type = 'admin')
    {
        $biz = ServiceKernel::instance()->getBiz();
        $adminPermissionYml = $biz['role.get_permissions_yml'][$type];

        foreach ($tree as &$child) {
            $child['name'] = $this->trans($child['name'], array(), 'menu');
            //插入老后台或新后台对应的权限配置permissions，用于前台设置角色权限附带上对应的另一个版本的权限
            $child['permissions'] = empty($adminPermissionYml[$child['code']]) ? array() : $adminPermissionYml[$child['code']];
            if (isset($child['children'])) {
                $child['children'] = $this->rolesTreeTrans($child['children'], $type);
            }
        }

        return $tree;
    }

    /**
     * @param $permissions  '新老后台角色权限树选中的Code'
     *
     * @return array
     *'根据部分节点查找所有父级节点'
     */
    public function getAllParentPermissions($permissions)
    {
        $tree = PermissionBuilder::instance()->getOriginPermissionTree();
        $res = $tree->toArray();
        $nodes = $this->splitRolesTreeNode($res['children']);

        foreach ($permissions as $permission) {
            $permissions = $this->getParentRoleCodeArray($permission, $nodes, $permissions);
        }
        $permissions = array_unique($permissions);
        $permissions = array_filter($permissions);

        return $permissions;
    }

    /**
     * @param $tree '后台menus树结构数组'
     *
     * @return array
     *               根据新老后台切换设置过滤多余的角色树
     */
    public function filterRoleTree($tree)
    {
        $backstageSetting = $this->getSettingService()->get('backstage', array('is_v2' => 0));
        $isV2 = $backstageSetting['is_v2'];
//        $isV2 = 0;
        foreach ($tree as $key => $child) {
            if (($isV2 && 'admin_v2' != $child['code']) || (!$isV2 && 'admin_v2' == $child['code'])) {
                unset($tree[$key]);
            }
        }

        return array_values($tree);
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getPermissionsYmlContent()
    {
        $permissions = array();

        $permissions['admin'] = $this->loadPermissionsFromAllConfig();

        $permissions['adminV2'] = $this->loadPermissionsFromAllConfig('adminV2');

        return $permissions;
    }

    /**
     * @param $tree '后台menus树结构数组'
     * @param array $permissions '分割散的树节点Array'
     *
     * @return array
     *
     * 分割树结构各个节点形成array(array('code'=>xxx,'parent'=>xxx))二维数组
     */
    public function splitRolesTreeNode($tree, &$permissions = array())
    {
        foreach ($tree as &$child) {
            $permissions[$child['code']] = array(
                'code' => $child['code'],
                'parent' => isset($child['parent']) ? $child['parent'] : null,
            );
            if (isset($child['children'])) {
                $child['children'] = $this->splitRolesTreeNode($child['children'], $permissions);
            }
        }

        return $permissions;
    }

    /**
     * @param $code  '树节点的code'
     * @param $permissions '分割散的树节点Array, splitRolesTreeNode'
     * @param array $parentCodes
     *
     * @return array 返回传入节点所有的父级节点code
     */
    public function getParentRoleCodeArray($code, $nodes, &$parentCodes = array())
    {
        if (!empty($nodes[$code]) && !empty($nodes[$code]['parent'])) {
            $parentCodes[] = $nodes[$code]['parent'];
            $parentCodes = $this->getParentRoleCodeArray($nodes[$code]['parent'], $nodes, $parentCodes);
        }

        return $parentCodes;
    }

    protected function loadPermissionsFromAllConfig($type = 'admin')
    {
        $configs = $this->getPermissionConfig($type);
        $permissions = array();
        foreach ($configs as $config) {
            if (!file_exists($config)) {
                continue;
            }
            $menus = Yaml::parse(file_get_contents($config));
            if (empty($menus)) {
                continue;
            }

            $permissions = array_merge($permissions, $menus);
        }

        return $permissions;
    }

    protected function getPermissionConfig($type = 'admin')
    {
        $configPaths = array();

        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        if ('admin' == $type) {
            $files = array(
                $rootDir.'/../permissions.yml',
            );
        } else {
            $files = array(
                $rootDir.'/../permissions_v2.yml',
            );
        }

        foreach ($files as $filepath) {
            if (is_file($filepath)) {
                $configPaths[] = $filepath;
            }
        }

        $count = $this->getAppService()->findAppCount();
        $apps = $this->getAppService()->findApps(0, $count);

        foreach ($apps as $app) {
            if ('plugin' != $app['type']) {
                continue;
            }

            if ('MAIN' !== $app['code'] && $app['protocol'] < 3) {
                continue;
            }

            if (!PluginVersionToolkit::dependencyVersion($app['code'], $app['version'])) {
                continue;
            }

            $code = ucfirst($app['code']);
            if ('admin' == $type) {
                $configPaths[] = "{$rootDir}/../plugins/{$code}Plugin/permissions.yml";
            } else {
                $configPaths[] = "{$rootDir}/../plugins/{$code}Plugin/permissions_v2.yml";
            }
        }

        return $configPaths;
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getRoleDao()
    {
        return $this->createDao('Role:RoleDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
