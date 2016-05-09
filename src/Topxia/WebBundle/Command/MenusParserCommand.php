<?php
namespace Topxia\WebBundle\Command;

use Topxia\Common\BlockToolkit;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Console\Input\StringInput;
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
        $configPaths[] = "{$rootDir}/src/Topxia/AdminBundle/Resources/config/menus_admin.yml";

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

        var_dump($menus);
    }
}