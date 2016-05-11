<?php
namespace Topxia\WebBundle\Command;

use Topxia\Common\BlockToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MenusParserCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:menus-parser');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $this->buildMenus();  
    }

    public function buildMenus()
    {
        $configPaths = array();
        $rootDir     = realpath(__DIR__.'/../../../../');
        $position = 'admin';
        // $configPaths[] = "{$rootDir}/src/Topxia/WebBundle/Resources/config/menus_{$position}.yml";
        $configPaths[] = "{$rootDir}/plugins/Vip/VipBundle/Resources/config/menus_{$position}.yml";

        // $configPaths[] = "{$rootDir}/src/Classroom/ClassroomBundle/Resources/config/menus_{$position}.yml";

        // $configPaths[] = "{$rootDir}/src/Custom/WebBundle/Resources/config/menus_{$position}.yml";
        // $configPaths[] = "{$rootDir}/src/Custom/AdminBundle/Resources/config/menus_{$position}.yml";

        $menus = array();

        foreach ($configPaths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $menu = Yaml::parse($path);

            if (empty($menu)) {
                continue;
            }

            $menus = array_merge($menus, $menu);
        }

        $environment = ServiceKernel::instance()->getEnvironment();

        $roots = array(
            "_admin_vip_list" => $menus["_admin_vip_list"],
            "_admin_vip_setting" => $menus["_admin_vip_setting"]
        );

        $menuTree = array();
        foreach ($roots as $key => $root) {
            $menuTree = array_merge($menuTree, $this->parseMenuChildren(array($key=>$root), $menus));
        }
        file_put_contents("{$rootDir}/app/cache/{$environment}/menus.yml", Yaml::dump($menuTree,10000));
    }

    protected function parseMenuChildren($parentMenu, $menus)
    {
        $parentCode = key($parentMenu);

        foreach ($menus as $key => $menu) {
            if($menu['parent'] == $parentCode){
                unset($menu['parent']);
                $parentMenu[$parentCode]["children"][$key] = $menu;
            }
        }

        if(isset($parentMenu[$parentCode]["children"])) {
            $children = $parentMenu[$parentCode]["children"];
            foreach ($children as $key => $menu) {
                $hasChildrenMenu = $this->parseMenuChildren(array($key => $menu), $menus);
                $parentMenu[$parentCode]["children"][$key] = array_values($hasChildrenMenu)[0];
            }
        }

        return $parentMenu;
    }
}