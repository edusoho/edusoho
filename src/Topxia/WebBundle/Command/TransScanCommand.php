<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Finder\FInder;

class TransScanCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'trans:scan' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $dirs = $this->getScanDirectories();
        $keywords = $this->scanTrans($dirs, $output);

        $this->printScanResult($keywords, $output);
    }

    protected function getScanDirectories()
    {
        $root = dirname(dirname(__DIR__));
        $dirs = array(
            'TopxiaWebBundle' => "{$root}/WebBundle/Resources/views",
        );

        return $dirs;
    }

    protected function printScanResult($keywords, $output)
    {
        $total = count($keywords);
        $keywords = array_values(array_unique($keywords));
        $uniqueTotal = count($keywords);

        $output->writeln("<info>共找到{$total}个语言串，去除重复语言串后，共有{$uniqueTotal}个语言串。</info>");
    }

    protected function scanTrans($dirs, $output)
    {
        $keywords = array();

        foreach ($dirs as $bundle => $dir) {
            $finder = new Finder();
            $finder->files()->in($dir);
            foreach ($finder as $file) {
                $content = file_get_contents($file->getRealpath());

                $matched = preg_match_all('/\{\{\s*\'(.*?)\'\s*\|\s*?trans.*?\}\}/', $content, $matches);

                if ($matched) {
                    $output->write("[{$bundle}] {$file->getRelativePathname()}");
                    $count = count($matches[1]);
                    $output->write("<info> ... {$count}</info>");
                    $output->writeln("");

                    $keywords = array_merge($keywords, $matches[1]);
                }

            }
        }

        return $keywords;
    }

}