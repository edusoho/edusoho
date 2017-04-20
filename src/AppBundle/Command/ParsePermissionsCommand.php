<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ParsePermissionsCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('util:parse-permissions')
            ->setDescription('获取权限名称和路由名称一致的routing')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $routings = $this->buildRoutings();

        foreach ($routings as $key => $routing) {
            if (isset($routing['permissions'])) {
                $permissions = $routing['permissions'];
                if (count($permissions) == 1 && $permissions[0] == $key) {
                    echo $key.'\n';
                }
            }
        }
    }

    public function buildRoutings()
    {
        $rootDir = realpath(__DIR__.'/../../../../');

        $configPaths = array();
        $configPaths[] = "{$rootDir}/src/Topxia/AdminBundle/Resources/config/routing.yml";

        $routings = array();

        foreach ($configPaths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $routingArray = Yaml::parse($path);

            if (empty($routingArray)) {
                continue;
            }

            $routings = array_merge($routings, $routingArray);
        }

        return $routings;
    }
}
