<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class GenerateXapiDataCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:xapi:generate')
            ->addArgument('verb', InputArgument::OPTIONAL, 'xapi 动作', 'purchased')
            ->addArgument('num', InputArgument::OPTIONAL, '生成的数量', 20000)
            ->setDescription('批量生成xapi数据，用于测试');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verb = $input->getArgument('verb');
        $num = $input->getArgument('num');
        $method = 'generate'.ucfirst($verb).'Data';

        $this->$method($num);

        $output->writeln('<info>生成完成</info>');
    }

    private function generatePurchasedData($num)
    {
        $titles = array(
            'course' => '测试课程',
            'classroom' => '测试班级',
        );

        $statements = array();
        for ($i = 0; $i < $num; ++$i) {
            $target_type = 0 == mt_rand(1, 100) % 2 ? 'course' : 'classroom';

            $statement = array(
                'verb' => 'purchased',
                'user_id' => mt_rand(1, 1000),
                'target_id' => mt_rand(1, 1000),
                'target_type' => $target_type,
                'status' => 'created',
                'occur_time' => strtotime('-'.mt_rand(1, 100).'days'),
                'context' => array(
                    'pay_amount' => $this->generateRandAmount(),
                    'title' => $titles[$target_type].mt_rand(1, 1000),
                ),
            );

            $statements[] = $statement;
        }

        $this->getXapiService()->batchCreateStatements($statements);
    }

    private function generateRandAmount()
    {
        return  round(mt_rand(1, 10000) / 33, 2);
    }

    /**
     * @return \Biz\Xapi\Service\XapiService
     */
    private function getXapiService()
    {
        return $this->getBiz()->service('Xapi:XapiService');
    }
}
