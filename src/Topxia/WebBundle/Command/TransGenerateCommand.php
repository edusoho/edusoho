<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Finder\FInder;
use Symfony\Component\Yaml\Yaml;

class TransGenerateCommand extends TransScanCommand
{

    protected function configure()
    {
        $this->setName ( 'trans:generate' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>正在扫描语言串:</comment>");

        $dirs = $this->getScanDirectories();
        $keywords = $this->scanTrans($dirs, $output);
        $this->printScanResult($keywords, $output);

        $output->writeln("\n<comment>正在生成语言文件:</comment>");

        $locale = $this->getLocale();
        $tranFile = dirname(__DIR__) . "/Resources/translations/messages.{$locale}.yml";
        $existTrans = array();
        if (!file_exists($tranFile)) {
            $output->writeln("创建语言文件 <info>{$tranFile}</info>");
        } else {
            $content = file_get_contents($tranFile);

            $yaml = new Yaml();
            $existTrans = $yaml->parse($content);

            $output->writeln("语言文件 <info>{$tranFile}</info> 已经存在，共有" . count($content) . "个语言串");
        }

        $addCount = 0;
        foreach ($keywords as $keyword) {
            if (array_key_exists($keyword, $existTrans)) {
                $output->writeln(" - {$keyword} ... <comment>已翻译</comment>");
                continue;
            }
            $output->writeln(" - {$keyword} <info>... 新增</info>");
            $addCount ++;

            $existTrans[$keyword] = $keyword;
        }

        $output->writeln('<info>共新增' . $addCount . '个语言串</info>');
        $output->writeln('<question>END</question>');

        $yaml = new Yaml();
        $content = $yaml->dump($existTrans);

        file_put_contents($tranFile, $content);

    }

    protected function getLocale()
    {
        $dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
        $content = file_get_contents($dir . '/app/config/parameters.yml');

        $matched = preg_match('/locale\s*?\:\s*?(\w+)/', $content, $matches);

        if (!$matched) {
            throw new \RuntimeException('locale未定义!');
        }

        return $matches[1];
    }

}