<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteUserCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('util:delete-user')
            ->setDescription('删除查询后的用户：--real 真正删除')
            ->addArgument(
                'nickname',
                InputArgument::REQUIRED,
                '查询用户名'
            )
            ->addOption(
                'real',
                InputArgument::OPTIONAL
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始</info>');
        $this->initServiceKernel();
        $real = $input->getOption('real');
        $nickname = $input->getArgument('nickname');

        $limit = 5000;
        $count = $this->getUserService()->countUsers(['nickname' => $nickname]);
        $totalPage = ceil($count/$limit);
        $output->writeln('<info>开始处理数据总数:'.$count.' 总页数:'.$totalPage.', 每页'.$limit.'条</info>');
        $this->deleteUser(0, $limit, 0, $totalPage, $output, $real, $nickname);
        $output->writeln('<info>处理数据结束</info>');

    }

    protected function deleteUser($start, $limit, $page = 0, $totalPage, $output, $real, $nickname)
    {
        $output->writeln('<info>正在处理第'.$page.'页, 共'.$totalPage.'页</info>');
        $users = $this->getUserService()->searchUsers(['excludeTypes' => ['system','delete'], 'nickname' => $nickname],[], $start, $limit, ['id','nickname']);

        foreach($users as $user){
            $output->writeln('<info>正在处理:'.$user['nickname'].'</info>');
            if (!$real) {
                continue;
            }
            $this->getUserService()->deleteUser($user['id']);
            $output->writeln('<info>-----'.$user['nickname'].'-处理成功-----</info>');
        }
        
        $output->writeln('<info>======第'.$page.'页, 处理完成======</info>');

        if($page >= $totalPage){
            $output->writeln('<info>数据处理完成</info>');
            return false;
        }

        sleep(1);
        ++$page;
        $this->deleteUser($page*$limit, $limit, $page, $totalPage, $output, $real, $nickname);
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }


}