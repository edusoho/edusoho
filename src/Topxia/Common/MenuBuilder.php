<?php
namespace Topxia\Common;

use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class MenuBuilder
{
    private $position = null;

    private $menus = array();

    public function __construct($position)
    {
        $this->position = $position;
    }

    public function getMenuChildren($code, $group = null)
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
        $position    = $this->position;
        $configPaths = array();

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

    public function getParentMenu($code)
    {
        $menus = $this->buildMenus();

        if (!isset($menus[$code]) || empty($menus[$code]['parent'])) {
            return array();
        }

        return $menus[$menus[$code]['parent']];
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
