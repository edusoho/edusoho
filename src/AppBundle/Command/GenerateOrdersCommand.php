<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;

class GenerateOrdersCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:generate-orders')
            ->addArgument('courseId', InputArgument::REQUIRED, '课程id')
            ->addArgument('index', InputArgument::REQUIRED, '数量');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $index = $input->getArgument('index');
        $courseId = $input->getArgument('courseId');

        $course = $this->getCourseService()->getCourse($courseId);

        for ($i = 0; $i < $index; ++$i) {
            $user = $this->getUserService()->getUserByLoginField('test_'.$i);

            if (!empty($user)) {
                $order = array(
                    'userId' => $user['id'],
                    'title' => $course['title'],
                    'amount' => $course['price'],
                    'targetType' => 'course',
                    'targetId' => $course['id'],
                    'payment' => 'none',
                    'coinRate' => 1,
                    'priceType' => 'RMB',
                    'totalPrice' => $course['price'],
                    'snPrefix' => 'C',
                );

                $order = $this->createOrder($order);
                $this->getOrderService()->updateOrder($order['id'], array(
                    'payment' => 'wxpay',
                ));
            }
        }
    }

    protected function createOrder($order)
    {
        try {
            return $this->getOrderService()->createOrder($order);
        } catch (\Exception $e) {
            echo $e->getMessage();
            sleep(1);

            return $this->createOrder($order);
        }
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order:OrderService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}
