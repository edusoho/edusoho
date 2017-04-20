<?php

namespace AppBundle\Command;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CountChangelogCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:count-changelog')
            ->setDescription('统计主程序和当前安装的插件的changelog中发布版本数量');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始统计changelog中发布版本数量</info>');

        $baseDir = realpath($this->getContainer()->getParameter('kernel.root_dir').'/..');
        $filename = 'CHANGELOG';

        $extPluginsCount = array( //班级和教学资料库插件合到主程序里面之前的迭代版本数量
            'ClassRoom' => 11,
            'MaterialLib' => 10,
        );

        $mainCount = $this->countChangelog($baseDir.'/'.$filename);
        $output->writeln("主程序累计发布版本<info>{$mainCount}</info>个");

        $pluginsCount = array_sum(array_values($extPluginsCount));

        foreach ($extPluginsCount as $key => $value) {
            $output->writeln("插件`{$key}`累计发布版本<info>{$value}</info>个");
        }

        $finder = new Finder();
        $finder->directories()->depth('== 0')->in($baseDir.'/plugins');

        foreach ($finder as $dir) {
            $pluginName = $dir->getRelativePathname();
            $pluginCount = $this->countChangelog($dir->getRealPath().'/'.$filename);
            if ($pluginCount === false) {
                $output->writeln("<error>插件`{$pluginName}`目录下没有{$filename}</error>");
            } else {
                $pluginsCount += $pluginCount;
                $output->writeln("插件`{$pluginName}`累计发布版本<info>{$pluginCount}</info>个");
            }
        }

        $output->writeln('<info>*************************************</info>');
        $totalCount = $mainCount + $pluginsCount;
        $output->writeln("<info>*</info>主程序和插件累计一共发布了<info>$totalCount</info>个版本<info>*</info>");
        $output->writeln('<info>*************************************</info>');

        $output->writeln('<info>统计完毕</info>');
    }

    protected function countChangelog($changelogFile)
    {
        if (file_exists($changelogFile)) {
            return preg_match_all("/(\d+\.\d+\.\d+\w*\s*[\x{ff08}\(]\d+-\d+-\d+)[\)\x{ff09}]/u", file_get_contents($changelogFile));
        } else {
            return false;
        }
    }
}
