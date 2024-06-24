<?php

namespace AppBundle\Command;

use Biz\UnifiedPayment\Service\UnifiedPaymentService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TradeRefundCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:trade-refund')
        ->addArgument('tradeSn', InputArgument::REQUIRED, '交易号')
        ->addArgument('refundAmount', InputArgument::REQUIRED, '退款金额')
        ->setDescription('手动退款');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $refund = $this->getUnifiedPaymentService()->refund([
            'tradeSn' => $input->getArgument('tradeSn'),
            'refundAmount' => $input->getArgument('refundAmount'),
        ]);
        $output->writeln('<info>退款结果：</info>'.json_encode($refund));
    }

    /**
     * @return UnifiedPaymentService
     */
    protected function getUnifiedPaymentService()
    {
        return $this->createService('UnifiedPayment:UnifiedPaymentService');
    }
}
