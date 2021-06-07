<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class AddMultiClassProductCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('add:multi_class_product')
            ->addArgument('num', InputArgument::OPTIONAL, '数量');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $num = $input->getArgument('num');

        for ($i = 1; $i <= $num; $i++) {
            $this->getMultiClassProductDao()->create(['title' => '测试产品'.$i, 'type' => 'normal']);
            $output->writeln("<info>生成第{$i}个成功</info>");
        }
    }

    protected function getMultiClassProductDao()
    {
        return $this->getBiz()->dao('MultiClass:MultiClassProductDao');
    }
}
