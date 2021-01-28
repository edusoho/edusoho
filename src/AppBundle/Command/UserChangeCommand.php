<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Console\Input\InputOption;

class UserChangeCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:user-change')
            ->addArgument('nickname', InputArgument::REQUIRED, '需要修改哪个用户名的用户')
            ->addArgument('field', InputArgument::REQUIRED, '需要改变的字段名(nickname, mobile, password, email)')
            ->addArgument('value', InputArgument::REQUIRED, '修改后的值')
            ->addOption('real', null, InputOption::VALUE_NONE, '是否执行')
            ->setDescription('变更用户字段');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $nickname = $input->getArgument('nickname');
        $field = $input->getArgument('field');
        $value = $input->getArgument('value');
        $real = $input->getOption('real');

        $user = $this->getUserService()->getUserByNickname($nickname);
        if (empty($user)) {
            throw new \InvalidArgumentException("用户名为`{$nickname}`的用户不存在！");
        }

        $fieldNames = array(
            'nickname' => '用户名',
            'email' => '邮箱',
            'mobile' => '手机号',
            'password' => '密码',
        );

        if (!array_key_exists($field, $fieldNames)) {
            throw new \InvalidArgumentException('field 参数不正确，只允许 nickname, mobile, password, email');
        }

        $output->writeln('<comment>修改的原用户为：</comment>');
        $output->writeln("  * 用户名：<info>{$user['nickname']}</info>");
        $output->writeln("  * 邮箱：<info>{$user['email']}</info>");
        $output->writeln("  * 手机号：<info>{$user['verifiedMobile']}</info>");

        $output->writeln("\n<comment>要修改的字段为：</comment>");
        $output->writeln("  * 字段名：<info>{$fieldNames[$field]}</info>");
        $output->writeln("  * 新的值：<info>{$value}</info>");

        if ($real) {
            switch ($field) {
                case 'nickname':
                    $this->getUserService()->changeNickname($user['id'], $value);
                    break;
                case 'mobile':
                    $this->getUserService()->changeMobile($user['id'], $value);
                    break;
                case 'password':
                    $this->getUserService()->changePassword($user['id'], $value);
                    break;
                case 'email':
                    $this->getUserService()->changeEmail($user['id'], $value);
                    break;
            }
            $output->writeln("\n<info>修改成功！</info>\n");
        } else {
            $output->writeln("\n<question>确认，请在命令后加上 --real 参数，执行修改！</question>\n");
        }
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
