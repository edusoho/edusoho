<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

class CutFileCommand extends BaseCommand
{
    protected function configure()
    {
        $this->addArgument(
                'line',
                InputArgument::OPTIONAL,
                '每个文件的行数'
            )->setName('topxia:cutfile');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>dump-init-sql开始</info>');
        $line = $input->getArgument('line', 15);

        $rootPath = __DIR__.'/../../../';
        $filepath = $rootPath.'web/install/edusoho_init.sql';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($filepath)) {
            $output->writeln('<info>文件不存在</info>');
        }

        $command = "rm -rf {$rootPath}web/install/edusoho_init_*.sql";
        $output->writeln("<info>{$command}</info>");
        exec($command);

        $contents = file($filepath);
        $totalLines = count($contents);
        $fileCount = ceil($totalLines / $line);
        $num = 0;
        for ($i = 0; $i < $fileCount; ++$i) {
            $lineStart = $i * $line;
            $lineEnd = ($i + 1) * $line > $totalLines ? $totalLines : ($i + 1) * $line;
            $isEmpty = true;
            for ($j = $lineStart; $j < $lineEnd; ++$j) {
                if (strlen($contents[$j]) > 1) {
                    file_put_contents($rootPath.'web/install/edusoho_init_'.$num.'.sql', $contents[$j], FILE_APPEND);
                    $isEmpty = false;
                }
            }
            $output->writeln('<info>生成edusoho_init_'.$num.'.sql</info>');
            if (!$isEmpty) {
                ++$num;
            }
        }

        $output->writeln('<info>dump-init-sql结束</info>');
    }
}
