<?php

namespace AppBundle\Command;

use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NoUsedRoutingCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:no-used-routing')
            ->setDescription('扫描无效的routing配置，指配置中的action方法在controller中不存在');
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
                if (isset($routing['defaults']['_controller'])) {
                    $controller = $routing['defaults']['_controller'];
                    $controller = explode(':', $controller);

                    $bandleName = $controller[0];
                    $controllerName = str_replace('/', '\\', $controller[1]);
                    $methodName = $controller[2];

                    $classExists = class_exists($bundls[$bandleName].'\\'.$controllerName.'Controller');
                    if (!$classExists) {
                        continue;
                    }

                    $classInfo = new \ReflectionClass($bundls[$bandleName].'\\'.$controllerName.'Controller');
                    $hasMethod = $classInfo->hasMethod($methodName.'Action');
                    // if(!$hasMethod){
                    // }
                }
            }
        }
    }
}
