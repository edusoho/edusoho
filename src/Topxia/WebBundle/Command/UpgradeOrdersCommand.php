<?php
namespace Topxia\WebBundle\Command;

use Topxia\System;
use Topxia\Common\BlockToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class UpgradeOrdersCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:upgrade-orders')
        	->addArgument('startTime', InputArgument::REQUIRED, '订单创建的起始时间')
			->addArgument('endTime', InputArgument::REQUIRED, '订单创建的截止时间')
			->setDescription('用于命令行中执行模拟回调');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$this->initServiceKernel();

    	$startTime = $input->getArgument('startTime');
		$endTime = $input->getArgument('endTime');

		$conditions = array();

		$total = $this->getOrderService()->searchOrderCount($conditions);
		$maxPage = ceil($total / 100) ? ceil($total / 100) : 1;
		$perPageNum = 20;


		for ($page=0; $page < $maxPage; $page++) { 
			$start = $page*$perPageNum;
			$orders = $this->getOrderService()->searchOrders($conditions, 'latest', $start, $perPageNum);
			$this->processOrders($orders);

		}
    }

    protected function processOrders($orders)
    {
    	foreach ($orders as $key => $order) {
    		if(in_array($order['payment'], array('wxpay'))) {

    			$options = $this->getPaymentOptions($order['payment']);

    			$payment = Payment::createTradeQueryRequest($order['payment'], $options);
    			$payment->setParams($order);

    			$result = $payment->tradeQuery();
    			var_dump($result);
    		}
    	}
    }

    private function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('prod', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());
        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id'        => 0,
            'nickname'  => '游客',
            'currentIp' => '127.0.0.1',
            'roles'     => array()
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }

    protected function getPaymentOptions($payment)
    {
        $settings = $this->getSettingService()->get('payment');

        $options = array(
            'key'    => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
        );

        return $options;
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}