<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckOrderPayUpgradeCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:check-order-pay-upgrade')
            ->setDescription('检查order-pay升级');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $biz = $this->getBiz();
        $connection = $biz['db'];
        $this->checkBizOrder($output, $biz, $connection);
    }

    protected function checkBizOrder($output, $biz, $connection)
    {
        $output->writeln('<info>biz_order：</info>');

        $countOrders = $connection->fetchColumn('select count(*) from orders');
        $countBizOrder = $connection->fetchColumn('select count(*) from biz_order');
        if ($countOrders == $countBizOrder) {
            $output->writeln("<info>orders表数量{$countOrders}, biz_order表数量{$countBizOrder}</info>");
        } else {
            $output->writeln("<error>orders表数量{$countOrders}, biz_order表数量{$countBizOrder}</error>");
        }

        $status = $connection->fetchAll('select status from biz_order group by status');
        $output->writeln('<info>biz_order的status字段：'.json_encode(ArrayToolkit::column($status, 'status')));

        $count = $connection->fetchColumn("select count(*) from biz_order where status='closed' and close_time=0");
        $output->writeln("<info>biz_order的closed状态下close_time=0的数量：{$count}");

        $count = $connection->fetchColumn("select count(*) from biz_order where status='closed' and close_user_id=0");
        $output->writeln("<info>biz_order的closed状态下close_user_id=0的数量：{$count}");

        $count = $connection->fetchColumn('select count(*) from biz_order where price_amount < pay_amount');
        $output->writeln("<info>biz_order的price_amount < pay_amount数量：{$count}");

        $payments = $connection->fetchAll('select payment from biz_order group by payment');
        $output->writeln('<info>biz_order的payment：'.json_encode(ArrayToolkit::column($payments, 'payment')));

        $payments = $connection->fetchAll("select payment from biz_order where status = 'success' group by payment");
        $output->writeln('<info>biz_order的success状态下的payment：'.json_encode(ArrayToolkit::column($payments, 'payment')));

        $payments = $connection->fetchAll("select payment from biz_order where status = 'success' and pay_amount>0 group by payment");
        $output->writeln("<info>biz_order的status = 'success' and pay_amount>0下的payment：".json_encode(ArrayToolkit::column($payments, 'payment')));

        $output->writeln('');
    }
}
