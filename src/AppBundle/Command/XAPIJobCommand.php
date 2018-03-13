<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class XAPIJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:xapi:run')
            ->addArgument('jobName', InputArgument::REQUIRED, '任务名称')
            ->setDescription('手动执行 xapi 的定时任务');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start_time =time();
        $biz = $this->getBiz();
        $jobName = $input->getArgument('jobName');
        $class = "Biz\\Xapi\\Job\\$jobName";
        /** @var \Codeages\Biz\Framework\Scheduler\AbstractJob $instance */
        $instance = new $class(array(), $biz);
        $instance->execute();

        $end_time = time();

        $output->writeln(sprintf('<info>Peak memory usage: %s</info>', $this->getNiceFileSize(memory_get_peak_usage())));

        $output->writeln(sprintf('<info>Time usage: %ss</info>', $end_time - $start_time));
    }

    function getNiceFileSize($bytes, $binaryPrefix = true) {
        if ($binaryPrefix) {
            $unit=array('B','KiB','MiB','GiB','TiB','PiB');
            if ($bytes==0) return '0 ' . $unit[0];
            return @round($bytes/pow(1024,($i=floor(log($bytes,1024)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
        } else {
            $unit=array('B','KB','MB','GB','TB','PB');
            if ($bytes==0) return '0 ' . $unit[0];
            return @round($bytes/pow(1000,($i=floor(log($bytes,1000)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
        }
    }
}
