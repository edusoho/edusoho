<?php

namespace AppBundle\Command;

use Biz\CloudPlatform\Service\AppService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class ConvertPermissionsCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('util:convert-permissions')
            ->addArgument('pluginCode', InputArgument::OPTIONAL, '插件code')
            ->setDescription('转换permission.yml配置,默认是转换edusoho下permission.yml，带参则转换插件');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $plugin = $input->getArgument('pluginCode');
        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $permissionPatch = $rootDir.'/../permissions.yml';

        if (!empty($plugin)) {
            $permissionPatch = "{$rootDir}/../plugins/{$plugin}Plugin/permissions.yml";
        }
        if (!file_exists($permissionPatch)) {
            throw new \InvalidArgumentException("{$permissionPatch}文件不存在！");
        }

        $output->writeln('<info>转换'.$permissionPatch.'的menus权限</info>');

        $this->convertOldPermissions($permissionPatch, $output);
    }

    protected function convertOldPermissions($patch, $output)
    {
        $ymlContent = Yaml::parse(file_get_contents($patch));
        file_put_contents($patch, Yaml::dump($ymlContent));

        $data = array();
        try {
            foreach ($ymlContent as $key => &$permissions) {
                foreach ($permissions as $permission) {
                    if (empty($data[$permission])) {
                        $data[$permission][] = $key;
                    } else {
                        $data[$permission] = array_merge($data[$permission], array($key));
                    }
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<info>请确认下文件内容是否符合要求</info>');
        }

        $patch = str_ireplace('permissions.yml', 'permissions_v2.yml', $patch);

        file_put_contents($patch, Yaml::dump($data));
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }
}
