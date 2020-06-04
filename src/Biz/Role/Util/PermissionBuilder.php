<?php

namespace Biz\Role\Util;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\PluginVersionToolkit;
use AppBundle\Common\Tree;
use Biz\CloudPlatform\Service\AppService;
use Biz\Role\Service\RoleService;
use Biz\System\Service\SettingService;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class PermissionBuilder
{
    private static $builder;
    private $cached = [];

    private function __construct()
    {
    }

    public static function instance()
    {
        if (empty(self::$builder)) {
            self::$builder = new self();
        }

        return self::$builder;
    }

    /**
     * @param array $roleCodes 角色Code
     *
     * @return array $permissions[]
     */
    public function getPermissionsByRoles(array $roleCodes)
    {
        if (empty($roleCodes)) {
            return [];
        }

        $permissionBuilder = self::instance();
        $originPermissions = $permissionBuilder->getOriginPermissions();

        if (in_array('ROLE_SUPER_ADMIN', $roleCodes)) {
            return $originPermissions;
        }

        $permissionCodes = $this->findPermissionCodesByRoleCodes($roleCodes);
        $permissions = [];
        foreach ($originPermissions as $key => $value) {
            if (in_array($key, $permissionCodes)) {
                $permissions[$key] = $value;
            }
        }

        return $permissions;
    }

    public function getSubPermissions($code, $group)
    {
        if (isset($this->cached['getSubPermissions'][$code][$group])) {
            return $this->cached['getSubPermissions'][$code][$group];
        }

        if (!isset($this->cached['getSubPermissions'])) {
            $this->cached['getSubPermissions'] = [];
        }

        if (!isset($this->cached['getSubPermissions'][$code])) {
            $this->cached['getSubPermissions'][$code] = [];
        }

        $userPermissionTree = $this->getUserPermissionTree();

        $codeTree = $userPermissionTree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        if (is_null($codeTree)) {
            return $this->cached['getSubPermissions'][$code];
        }

        $children = [];
        foreach ($codeTree->getChildren() as $child) {
            if (empty($group) || (isset($child->data['group']) && $child->data['group'] == $group)) {
                $children[] = $child->data;
            }
        }
        $childrenCodes = ArrayToolkit::column($children, 'code');
        $subPermission = $this->getOriginSubPermissions($code);

        foreach ($subPermission as $value) {
            $issetDisable = isset($value['disable']) && $value['disable'];
            $isGroup = empty($group) || (isset($value['group']) && $value['group'] == $group);
            $isExist = in_array($value['code'], $childrenCodes, true);
            if ($issetDisable && $isGroup && !$isExist) {
                $children[] = $value;
            }
        }

        $this->cached['getSubPermissions'][$code][$group] = $children;

        return $this->cached['getSubPermissions'][$code][$group];
    }

    public function groupedV2Permissions($code)
    {
        if (isset($this->cached['groupedPermissions'][$code])) {
            return $this->cached['groupedPermissions'][$code];
        }

        $this->cached['groupedPermissions'][$code] = [];

        $userPermissionTree = $this->getUserPermissionTree();
        $codeTree = $userPermissionTree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        if (is_null($codeTree)) {
            return $this->cached['groupedPermissions'][$code];
        }
        $grouped = [];
        foreach ($codeTree->getChildren() as $child) {
            $groupIndex = $child->data['code'];
            $grouped[$groupIndex] = $child->data;
            unset($grouped[$groupIndex]['children']);
            foreach ($child->getChildren() as $childMenu) {
                $groupIndex2 = $childMenu->data['code'];
                $grouped[$groupIndex]['children'][$groupIndex2] = $childMenu->data;
            }
        }
        $this->cached['groupedPermissions'][$code] = $grouped;

        return $this->cached['groupedPermissions'][$code];
    }

    public function groupedPermissions($code)
    {
        if (isset($this->cached['groupedPermissions'][$code])) {
            return $this->cached['groupedPermissions'][$code];
        }

        $this->cached['groupedPermissions'][$code] = [];

        $userPermissionTree = $this->getUserPermissionTree();

        $codeTree = $userPermissionTree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        if (is_null($codeTree)) {
            return $this->cached['groupedPermissions'][$code];
        }

        $grouped = [];

        foreach ($codeTree->getChildren() as $child) {
            $groupIndex = $child->data['group'];

            if (empty($grouped[$groupIndex])) {
                $grouped[$groupIndex] = [];
            }

            $grouped[$groupIndex][] = $child->data;
        }

        uksort(
            $grouped,
            function ($k1, $k2) {
                return $k1 > $k2 ? 1 : -1;
            }
        );

        $this->cached['groupedPermissions'][$code] = $grouped;

        return $this->cached['groupedPermissions'][$code];
    }

    public function getPermissionByCode($code)
    {
        if (isset($this->cached['getPermissionByCode'][$code])) {
            return $this->cached['getPermissionByCode'][$code];
        }

        if (!isset($this->cached['getPermissionByCode'])) {
            $this->cached['getPermissionByCode'] = [];
        }

        $this->cached['getPermissionByCode'][$code] = [];

        $userPermissionTree = $this->getUserPermissionTree();

        $codeTree = $userPermissionTree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        if (is_null($codeTree)) {
            return $this->cached['getPermissionByCode'][$code];
        }

        $this->cached['getPermissionByCode'][$code] = $codeTree->data;

        return $this->cached['getPermissionByCode'][$code];
    }

    public function getOriginSubPermissions($code, $group = null)
    {
        if (isset($this->cached['getOriginSubPermissions'][$code][$group])) {
            return $this->cached['getOriginSubPermissions'][$code][$group];
        }

        if (!isset($this->cached['getOriginSubPermissions'])) {
            $this->cached['getOriginSubPermissions'] = [];
        }

        if (!isset($this->cached['getOriginSubPermissions'][$code])) {
            $this->cached['getOriginSubPermissions'][$code] = [];
        }

        $tree = $this->getOriginPermissionTree(true);

        $codeTree = $tree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        $permissions = [];
        if (!is_null($codeTree)) {
            foreach ($codeTree->getChildren() as $child) {
                if (empty($group) || (isset($child->data['group']) && $child->data['group'] == $group)) {
                    $permissions[] = $child->data;
                }
            }
        }

        $this->cached['getOriginSubPermissions'][$code][$group] = $permissions;

        return $this->cached['getOriginSubPermissions'][$code][$group];
    }

    public function getPermissionConfig()
    {
        return array_merge($this->getMainPermissionConfig(), $this->getPluginPermissionConfig());
    }

    /**
     * @param bool $needDisable 树结构里是否需要包含权限管理被忽略的权限
     *
     * @return Tree
     */
    public function getOriginPermissionTree($needDisable = false)
    {
        $index = (int) $needDisable;
        if (isset($this->cached['getOriginPermissionTree'][$index])) {
            return $this->cached['getOriginPermissionTree'][$index];
        }

        $permissions = $this->getOriginPermissions();

        if (!$needDisable) {
            $permissions = array_filter(
                $permissions,
                function ($permission) {
                    return !(isset($permission['disable']) && $permission['disable']);
                }
            );
        }

        $tree = Tree::buildWithArray($permissions, null, 'code', 'parent');

        $this->cached['getOriginPermissionTree'][$index] = $tree;

        return $tree;
    }

    public function getOriginPermissions()
    {
        if (isset($this->cached['getOriginPermissions'])) {
            return $this->cached['getOriginPermissions'];
        }

        $environment = ServiceKernel::instance()->getEnvironment();
        $cacheDir = ServiceKernel::instance()->getParameter('kernel.cache_dir');
        $cacheFile = $cacheDir.'/menus_cache_admin.php';
        if ('dev' != $environment && file_exists($cacheFile)) {
            $this->cached['getOriginPermissions'] = include $cacheFile;

            return $this->cached['getOriginPermissions'];
        }

        $permissions = $this->loadPermissionsFromAllConfig();
        $this->cached['getOriginPermissions'] = $permissions;

        if (in_array($environment, ['test', 'dev'])) {
            return $permissions;
        }
        $cache = "<?php \nreturn ".var_export($permissions, true).';';
        file_put_contents($cacheFile, $cache);

        return $permissions;
    }

    public function loadPermissionsFromAllConfig()
    {
        $configs = $this->getPermissionConfig();
        $permissions = [];
        foreach ($configs as $config) {
            if (!file_exists($config)) {
                continue;
            }
            $menus = Yaml::parse(file_get_contents($config));
            if (empty($menus)) {
                continue;
            }

            $menus = $this->loadPermissionsFromConfig($menus);

            $permissions = array_merge($permissions, $menus);
        }

        return $permissions;
    }

    public function getOriginPermissionByCode($code)
    {
        $permissions = $this->getOriginPermissions();

        return isset($permissions[$code]) ? $permissions[$code] : [];
    }

    public function getParentPermissionByCode($code)
    {
        $userPermissionTree = $this->getUserPermissionTree();

        $codeTree = $userPermissionTree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        if (is_null($codeTree)) {
            return [];
        }

        $parent = $codeTree->getParent();

        if (is_null($parent)) {
            return [];
        }

        return $parent->data;
    }

    protected function loadPermissionsFromConfig($parents)
    {
        $menus = [];

        foreach ($parents as $key => $value) {
            $value['code'] = $key;
            $menus[$key] = $value;
            if (isset($value['children'])) {
                $childrenMenu = $value['children'];

                unset($value['children']);

                foreach ($childrenMenu as $childKey => $childValue) {
                    $childValue['parent'] = $key;
                    $menus = array_merge($menus, $this->loadPermissionsFromConfig([$childKey => $childValue]));
                }
            }
        }

        return $menus;
    }

    /**
     * 获取用户自身所拥有的权限树.
     *
     * @return Tree
     */
    public function getUserPermissionTree()
    {
        if (isset($this->cached['getUserPermissionTree'])) {
            return $this->cached['getUserPermissionTree'];
        }

        $menus = $this->loadPermissions();
        if (empty($menus)) {
            return new Tree();
        }
        $merchantDisabledPermissions = $this->getS2B2CFacadeService()->getMerchantDisabledPermissions();
        $i = 1;
        foreach ($menus as $code => &$menu) {
            if (is_array($merchantDisabledPermissions) && in_array($code, $merchantDisabledPermissions)) {
                unset($menus[$code]);
                continue;
            }
            $menu['code'] = $code;
            $menu['weight'] = $i * 100;

            if (empty($menu['group'])) {
                $menu['group'] = 1;
            }

            ++$i;
            unset($menu);
        }

        foreach ($menus as &$menu) {
            if (!empty($menu['before']) && !empty($menus[$menu['before']]['weight'])) {
                $menu['weight'] = $menus[$menu['before']]['weight'] - 1;
            } elseif (!empty($menu['after']) && !empty($menus[$menu['after']]['weight'])) {
                $menu['weight'] = $menus[$menu['after']]['weight'] + 1;
            }

            unset($menu);
        }

        uasort(
            $menus,
            function ($a, $b) {
                return $a['weight'] > $b['weight'] ? 1 : -1;
            }
        );

        $userPermissionTree = Tree::buildWithArray($menus, null, 'code', 'parent');
        $this->cached['getUserPermissionTree'] = $userPermissionTree;

        return $this->cached['getUserPermissionTree'];
    }

    /**
     * @return array
     *               获取主程序的menus
     */
    protected function getMainPermissionConfig()
    {
        $configPaths = [];

        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $files = [
            $rootDir.'/../src/AppBundle/Resources/config/menus_admin.yml',
            $rootDir.'/../src/AppBundle/Resources/config/menus_admin_v2.yml',
            $rootDir.'/../src/CustomBundle/Resources/config/menus_admin.yml',
            $rootDir.'/../src/CustomBundle/Resources/config/menus_admin_v2.yml',
        ];

        foreach ($files as $filepath) {
            if (is_file($filepath)) {
                $configPaths[] = $filepath;
            }
        }

        return $configPaths;
    }

    /**
     * @return array
     *               获取插件的menus
     */
    protected function getPluginPermissionConfig()
    {
        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $configPaths = [];
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
            $configPaths[] = "{$rootDir}/../plugins/{$code}Plugin/Resources/config/menus_admin.yml";
            $configPaths[] = "{$rootDir}/../plugins/{$code}Plugin/Resources/config/menus_admin_v2.yml";
        }

        return $configPaths;
    }

    /**
     * @param $roleCodes
     *
     * @return array
     *               根据role.code批量获取对应的permissionCodes
     */
    protected function findPermissionCodesByRoleCodes($roleCodes)
    {
        $permissionCodes = [];
        $roles = $this->getRoleService()->findRolesByCodes($roleCodes);
        $field = $this->isAdminV2() ? 'data_v2' : 'data';
        foreach ($roles as $role) {
            if (!empty($role[$field])) {
                $permissionCodes = array_merge($permissionCodes, $role[$field]);
            }
        }

        return $permissionCodes;
    }

    private function loadPermissions()
    {
        $user = $this->getServiceKernel()->getCurrentUser();

        return $user->getPermissions();
    }

    private function isAdminV2()
    {
        $backstage = $this->getSettingService()->get('backstage', ['is_v2' => 0]);

        return $backstage['is_v2'] ? true : false;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform:AppService');
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->getServiceKernel()->createService('Role:RoleService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->getServiceKernel()->createService('S2B2C:S2B2CFacadeService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
