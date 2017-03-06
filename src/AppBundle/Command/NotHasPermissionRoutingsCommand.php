<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Yaml\Yaml;

class NotHasPermissionRoutingsCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:not-has-permission')
            ->setDescription('扫描未配置permission的routing配置');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $rootPath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../';

        $configs = array(
            $rootPath.'src/Topxia/WebBundle/Resources/config/routing.yml',
            $rootPath.'src/Topxia/AdminBundle/Resources/config/routing.yml',
            $rootPath.'src/Classroom/ClassroomBundle/Resources/config/routing.yml',
            $rootPath.'src/Classroom/ClassroomBundle/Resources/config/routing_admin.yml',
            $rootPath.'src/MaterialLib/MaterialLibBundle/Resources/config/routing.yml',
            $rootPath.'src/MaterialLib/MaterialLibBundle/Resources/config/routing_admin.yml',
        );

        $bundls = array(
            'TopxiaWebBundle' => 'Topxia\\WebBundle\\Controller',
            'TopxiaAdminBundle' => 'Topxia\\AdminBundle\\Controller',
            'ClassroomBundle' => 'Classroom\\ClassroomBundle\\Controller',
            'MaterialLibBundle' => 'MaterialLib\\MaterialLibBundle\\Controller',
        );

        foreach ($configs as $routingConfig) {
            $routings = Yaml::parse($routingConfig);

            if (empty($routings)) {
                continue;
            }

            foreach ($routings as $key => $routing) {
                if (!isset($routing['permissions'])) {
                    echo $key.'\n';
                }
            }
        }
    }
}
