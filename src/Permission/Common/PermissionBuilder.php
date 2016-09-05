<?php
namespace Permission\Common;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Topxia\Common\Tree;
use Topxia\Service\Common\ServiceKernel;

class PermissionBuilder
{
    private $position = 'admin';

    private static $builder;
    private        $cached = array();

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

    public function getSubPermissions($code, $group)
    {
        if (isset($this->cached['getSubPermissions'][$code][$group])) {
            return $this->cached['getSubPermissions'][$code][$group];
        }

        $menus = $this->buildPermissions();

        if (!isset($menus[$code])) {
            return array();
        }

        $children = array();

        foreach ($menus[$code]['children'] as $childCode) {
            if (!empty($group)
                && isset($menus[$childCode]['group'])
                && $menus[$childCode]['group'] != $group
            ) {
                continue;
            }

            $children[] = $menus[$childCode];
        }

        if (!isset($this->cached['getSubPermissions'])) {
            $this->cached['getSubPermissions'] = array();
        }

        if (!isset($this->cached['getSubPermissions'][$code])) {
            $this->cached['getSubPermissions'][$code] = array();
        }

        $this->cached['getSubPermissions'][$code][$group] = $children;

        return $this->cached['getSubPermissions'][$code][$group];
    }

    public function groupedPermissions($code)
    {
        if (isset($this->cached['groupedPermissions'][$code])) {
            return $this->cached['groupedPermissions'][$code];
        }

        $menus = $this->buildPermissions();

        if (!isset($menus[$code])) {
            return array();
        }

        $children = array();

        foreach ($menus[$code]['children'] as $childCode) {
            $children[] = $menus[$childCode];
        }

        return $this->groupPermissions($children);
    }

    public function getPermissionByCode($code)
    {
        if (isset($this->cached['getPermissionByCode'][$code])) {
            return $this->cached['getPermissionByCode'][$code];
        }

        $menus = $this->buildPermissions();

        if (!isset($menus[$code])) {
            return array();
        }

        if (!isset($this->cached['getPermissionByCode'])) {
            $this->cached['getPermissionByCode'] = array();
        }
        $this->cached['getPermissionByCode'][$code] = $menus[$code];
        return $menus[$code];
    }

    public function getPermissionConfig()
    {
        $configPaths = array();
        $position    = $this->position;

        $rootDir = realpath(__DIR__ . '/../../../');

        $finder = new Finder();
        $finder->directories()->depth('== 0');

        if (glob($rootDir . '/src/*/*/Resources', GLOB_ONLYDIR)) {
            $finder->in($rootDir . '/src/*/*/Resources');
        }

        foreach ($finder as $dir) {
            $filepath = $dir->getRealPath() . "/menus_{$position}.yml";
            if (file_exists($filepath)) {
                $configPaths[] = $filepath;
            }
        }

        $count = $this->getAppService()->findAppCount();
        $apps  = $this->getAppService()->findApps(0, $count);

        foreach ($apps as $app) {
            if ($app['type'] != 'plugin') {
                continue;
            }

            $code          = ucfirst($app['code']);
            $configPaths[] = "{$rootDir}/plugins/{$code}/{$code}Bundle/Resources/config/menus_{$position}.yml";
        }

        return $configPaths;
    }

    /**
     * @return Tree
     */
    public function getOriginPermissionTree()
    {
        if (isset($this->cached['getOriginPermissionTree'])) {
            return $this->cached['getOriginPermissionTree'];
        }

        $permissions = $this->getOriginPermissions();

        $tree = Tree::buildWithArray($permissions, null, 'code', 'parent');

        $this->cached['getOriginPermissionTree'] = $tree;
        return $tree;
    }

    public function getOriginPermissions()
    {
        if (isset($this->cached['getOriginPermissions'])) {
            return $this->cached['getOriginPermissions'];
        }

        $environment = ServiceKernel::instance()->getEnvironment();
        $cacheFile   = "../app/cache/" . $environment . "/menus_" . $this->position . ".php";
        if ($environment != "dev" && file_exists($cacheFile)) {
            $this->cached['getOriginPermissions'] = include $cacheFile;
            return $this->cached['getOriginPermissions'];
        }

        $configs     = $this->getPermissionConfig();
        $permissions = array();
        foreach ($configs as $key => $config) {
            if (!file_exists($config)) {
                continue;
            }
            $menus = Yaml::parse(file_get_contents($config));
            if (empty($menus)) {
                continue;
            }

            $menus       = $this->loadPermissionsFromConfig($menus);
            $permissions = array_merge($permissions, $menus);
        }

        $this->cached['getOriginPermissions'] = $permissions;

        if (in_array($environment, array('test', 'dev'))) {
            return $permissions;
        }

        $cache = "<?php \nreturn " . var_export($permissions, true) . ';';
        file_put_contents($cacheFile, $cache);

        return $permissions;
    }

    /*protected function getPermissionTree(&$tree, $roots, $menus)
    {
        $id = 0;

        foreach ($roots as $key => $root) {
            $id++;
            $rootNode           = $menus[$root];
            $rootNode['id']     = $id;
            $rootNode['code']   = $root;
            $rootNode['parent'] = null;
            $tree[]             = $rootNode;

            $this->getSubTree($tree, $id, $rootNode, $menus);
        }

        return $tree;
    }

    protected function getSubTree(&$tree, &$id, $parentNode, $menus)
    {
        foreach ($menus as $key => &$menu) {
            if ($menu['parent'] == $parentNode['code']) {
                $id++;
                $menu['id']   = $id;
                $menu['pId']  = $parentNode['id'];
                $menu['code'] = $key;
                $tree[]       = $menu;

                $this->getSubTree($tree, $id, $menu, $menus);
            }
        }
    }*/

    public function getParentPermissionByCode($code)
    {
        $menus = $this->buildPermissions();

        if (!isset($menus[$code]) || empty($menus[$code]['parent'])) {
            return array();
        }

        return $menus[$menus[$code]['parent']];
    }

    protected function loadPermissionsFromConfig($parents)
    {
        $menus = array();

        foreach ($parents as $key => $value) {
            if (isset($value['children'])) {
                $childrenMenu = $value['children'];

                unset($value['children']);

                foreach ($childrenMenu as $childKey => $childValue) {
                    $childValue["parent"] = $key;
                    $menus                = array_merge($menus, $this->loadPermissionsFromConfig(array($childKey => $childValue)));
                }
            }
            $value['code'] = $key;
            $menus[$key] = $value;
        }

        return $menus;
    }

    private function groupPermissions($menus)
    {
        $grouped = array();

        foreach ($menus as $menu) {
            $groupIndex = $menu['group'];

            if (empty($grouped[$groupIndex])) {
                $grouped[$groupIndex] = array();
            }

            $grouped[$groupIndex][] = $menu;
        }

        uksort($grouped, function ($k1, $k2) {
            return $k1 > $k2 ? 1 : -1;
        }

        );

        return $grouped;
    }

    public function getAllPermissions()
    {
        $menus = $this->loadPermissions();

        if (isset($this->cached['getAllPermissions'])) {
            return $this->cached['getAllPermissions'];
        };

        $i = 1;
        foreach ($menus as $code => &$menu) {
            $menu['code']     = $code;
            $menu['weight']   = $i * 100;

            if (empty($menu['group'])) {
                $menu['group'] = 1;
            }

            $i++;
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

        $this->cached['getAllPermissions'] = $menus;
        return $menus;
    }

    /**
     * 时间复杂度O(n)  n 为 用户所拥有的权限
     * @return array
     */
    private function buildPermissions()
    {
        $menus = $this->loadPermissions();

        if (empty($menus)) {
            return array();
        }

        $i = 1;
        foreach ($menus as $code => &$menu) {
            $menu['code']     = $code;
            $menu['weight']   = $i * 100;
            $menu['children'] = array();

            if (empty($menu['group'])) {
                $menu['group'] = 1;
            }

            $i++;
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

        uasort($menus, function ($a, $b) {
            return $a['weight'] > $b['weight'] ? 1 : -1;
        });

        foreach ($menus as $code => $menu) {
            if (empty($menu['parent'])) {
                continue;
            }

            if (!isset($menus[$menu['parent']])) {
                continue;
            }

            $menus[$menu['parent']]['children'][] = $code;
        };

        return $menus;
    }

    private function loadPermissions()
    {
        $user = $this->getServiceKernel()->getCurrentUser();
        return $user->getPermissions();
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
