<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Topxia\Service\User\CurrentUser;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class UserConfuseCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'crm:userConfuse' )
        ->setDescription('crm:混淆用户数据');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('数据混淆开始 .');
        $this->db()->executeUpdate("UPDATE `user` SET `email` =CONCAT(`id`, '@edusoho.net')");
        $this->db()->executeUpdate("UPDATE user_profile up SET `truename` = (SELECT nickname FROM user where id = up.id ) WHERE ( truename <>'')");
        $this->db()->executeUpdate("UPDATE `user_profile` SET `mobile` = 13967340627 WHERE ( mobile <>'')");
        $this->db()->executeUpdate("UPDATE `user` SET `verifiedMobile` = 13967340627 WHERE ( verifiedMobile <>'')");
        $output->writeln('数据混淆成功 .');
    }

    protected function db()
    {
       return $this->getContainer()->get('database_connection');
    }
}
