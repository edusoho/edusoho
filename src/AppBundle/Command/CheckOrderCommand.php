<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CheckOrderCommand extends BaseCommand
{
    private $host = '';

    protected function configure()
    {
        $this->setName('util:check-orders')
            ->addArgument('filePath', InputArgument::REQUIRED, '文件地址')
            ->addArgument('logFile', InputArgument::REQUIRED, 'log文件地址')
            ->setDescription('用于命令行中执行模拟回调');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $filePath = $input->getArgument('filePath');
        $logFile = $input->getArgument('logFile');

        $file = file($filePath);
        foreach ($file as &$sn) {
            file_put_contents($logFile, '订单号：'.trim($sn).PHP_EOL, FILE_APPEND);
            $order = $this->getOrderService()->getOrderBySn(trim($sn));
            file_put_contents($logFile, '订单类型：'.$order['targetType'].PHP_EOL, FILE_APPEND);
            file_put_contents($logFile, '订单信息：'.json_encode($order).PHP_EOL, FILE_APPEND);
            if ($order['targetType'] == 'course') {
                $member = $this->getCourseMemberService()->getCourseMember($order['targetId'], $order['userId']);
                if (empty($member)) {
                    file_put_contents($logFile, '学员没加入课程'.PHP_EOL, FILE_APPEND);
                } else {
                    file_put_contents($logFile, '课程学员：'.json_encode($member).PHP_EOL, FILE_APPEND);
                }
            } else {
                file_put_contents($logFile, "{$order['sn']} 的学员没加入课程".PHP_EOL, FILE_APPEND);
            }
        }
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order:OrderService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }
}
