<?php

namespace AppBundle\Command;

use Biz\Role\RoleService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;

class RoleRefreshCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('role:refresh')
            ->setDescription('根据菜单配置，更新默认权限');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始刷新默认权限</info>');
        $this->getRoleService()->refreshRoles();
        $output->writeln('<info>刷新成功...</info>');
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return ServiceKernel::instance()->createService('Role:RoleService');
    }
}
