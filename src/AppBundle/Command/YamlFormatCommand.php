<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class YamlFormatCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('util:yaml-format')
            ->addArgument('filePath', InputArgument::OPTIONAL, '文件地址')
            ->setDescription('格式化Yaml,兼容symfony4.0以及更高版本');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('filePath');
        if ($filePath) {
            $content = Yaml::parseFile($filePath);
            $dump = Yaml::dump($content, 10, 4, 0);
            file_put_contents($filePath, $dump);

            return;
        }
        $rootPath = $this->getContainer()->getParameter('kernel.root_dir');
        $routingPaths = [
            'app_routing' => "{$rootPath}/../src/AppBundle/Resources/config/routing.yml",
            'app_routing_admin' => "{$rootPath}/../src/AppBundle/Resources/config/routing_admin.yml",
            'app_routing_admin_v2' => "{$rootPath}/../src/AppBundle/Resources/config/routing_admin_v2.yml",
        ];
        $parameterPaths = [
            'config' => "{$rootPath}/config/config.yml",
            'config_prod' => "{$rootPath}/config/config_prod.yml",
            'config_dev' => "{$rootPath}/config/config_dev.yml",
            'config_test' => "{$rootPath}/config/config_test.yml",
            'security' => "{$rootPath}/config/security.yml",
            'service_listener' => "{$rootPath}/config/service_listener.yml",
            'app_bundle_service' => "{$rootPath}/../src/AppBundle/Resources/config/services.yml",
            'app_bundle_event_subscriber' => "{$rootPath}/../src/AppBundle/Resources/config/event_subscribers.yml",
            'app_bundle_log_modules' => "{$rootPath}/../src/AppBundle/Resources/config/log_modules.yml",
            'app_bundle_oauth' => "{$rootPath}/../src/AppBundle/Resources/config/oauth2.yml",
            'api_bundle_service' => "{$rootPath}/../src/ApiBundle/Resources/config/services.yml",
        ];
        foreach ($routingPaths as $key => $routingPath) {
            $content = Yaml::parseFile($routingPath);
            $dump = Yaml::dump($content, 10, 4, 0);
            file_put_contents($routingPath, $dump);
        }

        foreach ($parameterPaths as $key => $parameterPath) {
            $content = Yaml::parseFile($parameterPath);
            $dump = Yaml::dump($content, 10, 4, 0);
            file_put_contents($parameterPath, $dump);
        }
    }
}
