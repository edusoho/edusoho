<?php
namespace Topxia\Common;

use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class MenuBuilder
{
    private $position = 'admin';

    private $menus = array();

    public function getMenuChildren($code, $group = '1')
    {
        $menus = $this->buildMenus();

        if (!isset($menus[$code])) {
            return array();
        }

        $children = array();

        foreach ($menus[$code]['children'] as $childCode) {
            if ($group && $menus[$childCode]['group'] != $group) {
                continue;
            }

            $children[] = $menus[$childCode];
        }

        if (!$group) {
            $children = $this->groupMenus($children);
        }

        return $children;
    }

    public function groupedMenus($code)
    {
        $menus = $this->buildMenus();

        if (!isset($menus[$code])) {
            return array();
        }

        $children = array();

        foreach ($menus[$code]['children'] as $childCode) {
            $children[] = $menus[$childCode];
        }

        return $this->groupMenus($children);
    }

    public function getMenuByCode($code)
    {
        $menus = $this->buildMenus();

        if (!isset($menus[$code])) {
            return array();
        }

        return $menus[$code];
    }

    public function getMenusYml()
    {
        $configPaths = array();
        $position = $this->position;

        $rootDir = realpath(__DIR__.'/../../../');
        $configPaths[] = "{$rootDir}/src/Topxia/WebBundle/Resources/config/menus_{$position}.yml";
        $configPaths[] = "{$rootDir}/src/Topxia/AdminBundle/Resources/config/menus_{$position}.yml";

        $configPaths[] = "{$rootDir}/src/Classroom/ClassroomBundle/Resources/config/menus_{$position}.yml";
        $configPaths[] = "{$rootDir}/src/MaterialLib/MaterialLibBundle/Resources/config/menus_{$position}.yml";
        $configPaths[] = "{$rootDir}/src/SensitiveWord/SensitiveWordBundle/Resources/config/menus_{$position}.yml";

        $count         = $this->getAppService()->findAppCount();
        $apps          = $this->getAppService()->findApps(0, $count);

        foreach ($apps as $app) {
            if ($app['type'] != 'plugin') {
                continue;
            }

            $code          = ucfirst($app['code']);
            $configPaths[] = "{$rootDir}/plugins/{$code}/{$code}Bundle/Resources/config/menus_{$position}.yml";
        }

        $configPaths[] = "{$rootDir}/src/Custom/WebBundle/Resources/config/menus_{$position}.yml";
        $configPaths[] = "{$rootDir}/src/Custom/AdminBundle/Resources/config/menus_{$position}.yml";

        return $configPaths;

    }

    public function getOriginPermissionTree()
    {
        $permissions = $this->getOriginMenus();
        $tree = array();
        return $this->getMenuTree($tree, 'admin', $permissions);
    }

    public function getOriginMenus()
    {
        $configs = $this->getMenusYml();
        $res = array();
        foreach ($configs as $key => $config) {
            if(!file_exists($config)) {
                continue;
            }
            $menus = Yaml::parse(file_get_contents($config));
            if(empty($menus)) {
                continue;
            }

            $menus = $this->getMenusFromConfig($menus);
            $res = array_merge($res, $menus);
        }

        return $res;
    }


    protected function getMenuTree(&$tree, $root, $menus)
    {
        $id = 0;
        $node = $menus[$root];
        $node['id'] = $id;
        $node['code'] = $root;
        $node['parent'] = null;
        $tree[] = $node;

        foreach ($menus as $key => &$menu) {
            if($menu['parent'] == $root) {
                $id++;
                $menu['id'] = $id;
                $menu['pId'] = $node['id'];
                $menu['code'] = $key;
                $tree[] = $menu;

                $this->getSubTree($tree, $id, $menu, $menus);
            }
        }

        $tree = ArrayToolkit::index($tree, 'id');

        return $tree;
    }

    protected function getSubTree(&$tree, &$id, $parentNode, $menus)
    {
        foreach ($menus as $key => &$menu) {
            if($menu['parent'] == $parentNode['code']) {
                $id++;
                $menu['id'] = $id;
                $menu['pId'] = $parentNode['id'];
                $menu['code'] = $key;
                $tree[] = $menu;

                $this->getSubTree($tree, $id, $menu, $menus);
            }
        }
    }

    public function getParentMenu($code)
    {
        $menus = $this->buildMenus();

        if (!isset($menus[$code]) || empty($menus[$code]['parent'])) {
            return array();
        }

        return $menus[$menus[$code]['parent']];
    }

    protected function getMenusFromConfig($parents)
    {
        $menus = array();

        foreach ($parents as $key => $value) {
            if(isset($value['children'])) {
                $childrenMenu = $value['children'];
                unset($value['children']);

                foreach ($childrenMenu as $childKey => $childValue) {
                    $childValue["parent"] = $key;
                    $menus = array_merge($menus, $this->getMenusFromConfig(array($childKey => $childValue)));
                }
            }

            $menus[$key] = $value;
        }

        return $menus;
    }

    private function groupMenus($menus)
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

    private function buildMenus()
    {
        if ($this->menus) {
            return $this->menus;
        }

        $environment = ServiceKernel::instance()->getEnvironment();

        $cacheFile = "../app/cache/".$environment."/menus_".$this->position.".php";

        if ($environment != "dev" && file_exists($cacheFile)) {
            return include $cacheFile;
        }

        $menus = $this->loadMenus();
        if(empty($menus)) {
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
        }

        );

        foreach ($menus as $code => $menu) {
            if (empty($menu['parent'])) {
                continue;
            }

            if (!isset($menus[$menu['parent']])) {
                continue;
            }

            $menus[$menu['parent']]['children'][] = $code;
        }

        $this->menus = $menus;

        if (in_array($environment, array('test','dev'))) {
            return $menus;
        }

        $cache = "<?php \nreturn ".var_export($menus, true).';';
        file_put_contents($cacheFile, $cache);

        return $menus;
    }

    private function loadMenus()
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
