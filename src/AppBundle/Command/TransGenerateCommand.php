<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\FInder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransGenerateCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('trans:generate')
            ->addArgument('dir', InputArgument::REQUIRED, 'Bundle Directory')
            ->addArgument('type', InputArgument::OPTIONAL, 'Trans Type')
            ->setDescription('生成Bundle的语言文件! eg. src/Topxia/WebBundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument('dir');
        $type = $input->getArgument('type') == 'untranslate' ? $input->getArgument('type') : 'default';
        $dir = rtrim($dir, "\/");

        $output->writeln('<comment>正在扫描模版文件的语言串:</comment>');
        $viewKeywords = $this->scanViewTrans($dir, $output);
        $this->printScanResult($viewKeywords, $output);

        $output->writeln('<comment>正在扫描模版文件Radio的语言串:</comment>');
        $viewRadioKeywords = $this->scanViewRadioTrans($dir, $output);
        $this->printScanResult($viewRadioKeywords, $output);

        $output->writeln('<comment>正在扫描模版文件Default的语言串:</comment>');
        $viewDefaultKeywords = $this->scanViewDefaultTrans($dir, $output);
        $this->printScanResult($viewDefaultKeywords, $output);

        $output->writeln("<comment>\n正在扫描菜单配置文件的语言串:</comment>");
        $menuKeywords = $this->scanMenuTrans($dir, $output);
        $this->printScanResult($menuKeywords, $output);

        $output->writeln("<comment>\n正在扫描PHP文件的语言串:</comment>");
        $phpKeywords = $this->scanPHPTrans($dir, $output);
        $this->printScanResult($phpKeywords, $output);

        $output->writeln("<comment>\n正在扫描JS文件的语言串:</comment>");
        $jsKeywords = $this->scanJSTrans($dir, $output);
        $this->printScanResult($jsKeywords, $output);

        $output->writeln("<comment>\n正在扫描数据字典配置文件的语言串:</comment>");
        $dictsKeywords = $this->scanDictTrans($dir, $output);
        $this->printScanResult($menuKeywords, $output);

        $output->writeln("<comment>\n语言串总和:</comment>");
        $keywords = array_merge($viewKeywords, $viewRadioKeywords, $viewDefaultKeywords, $menuKeywords, $dictsKeywords, $phpKeywords, $jsKeywords);

        $this->printScanResult($keywords, $output);
        $keywords = array_values(array_unique($keywords));

        $output->writeln("\n<comment>正在生成语言文件:</comment>");

        $locale = $this->getLocale();

        //去掉WebBundle已有的key
        if ($dir != 'src/Topxia/WebBundle') {
            $webTransFile = sprintf('%s/%s/Resources/translations/messages.%s.yml', dirname($this->getRootDir()), 'src/Topxia/WebBundle', $locale);
            $webBundleTrans = array();
            if (!file_exists($webTransFile)) {
                $output->writeln('WebBundle语言包不存在!');
            } else {
                $content = file_get_contents($webTransFile);

                $yaml = new Yaml();
                $webBundleTrans = $yaml->parse($content);
                $output->writeln('扫描WebBundle语言包');
            }

            $addCount = 0;
            $webExistCount = 0;
            foreach ($keywords as $key => $keyword) {
                if (array_key_exists($keyword, $webBundleTrans)) {
                    ++$webExistCount;
                    unset($keywords[$key]);
                }
            }

            $output->writeln("<info>共找到和WebBundle重叠的语言串为: {$webExistCount}</info>");
        }

        $tranFile = sprintf('%s/%s/Resources/translations/messages.%s.yml', dirname($this->getRootDir()), $dir, $locale);

        $existTrans = array();

        if (!file_exists($tranFile)) {
            $output->writeln("创建语言文件 <info>{$tranFile}</info>");
        } else {
            $content = file_get_contents($tranFile);

            $yaml = new Yaml();
            $existTrans = $yaml->parse($content);

            $output->writeln("语言文件 <info>{$tranFile}</info> 已经存在");
        }

        $addCount = 0;
        $existCount = 0;
        $newTrans = array();
        $unTrans = array();
        foreach ($keywords as $keyword) {
            if (array_key_exists($keyword, $existTrans)) {
                ++$existCount;
                if ($type == 'untranslate' && $existTrans[$keyword] == $keyword) {
                    $unTrans[$keyword] = $existTrans[$keyword];
                } else {
                    $newTrans[$keyword] = $existTrans[$keyword];
                }
            } else {
                $output->writeln(" - {$keyword} <info>... 新增</info>");
                ++$addCount;
                if ($type == 'untranslate') {
                    $unTrans[$keyword] = $keyword;
                } else {
                    $newTrans[$keyword] = $keyword;
                }
            }
        }

        $output->writeln("<info>已存在{$existCount}个语言串，本次新增{$addCount}个语言串</info>");
        $output->writeln('<question>END</question>');
        $yaml = new Yaml();
        $content = $yaml->dump($newTrans);
        if ($type == 'untranslate') {
            $content .= $yaml->dump($unTrans);
        }
        file_put_contents($tranFile, $content);
    }

    protected function getLocale()
    {
        $content = file_get_contents($this->getRootDir().'/config/parameters.yml');

        $matched = preg_match('/locale\s*?\:\s*?(\w+)/', $content, $matches);

        if (!$matched) {
            throw new \RuntimeException('locale未定义!');
        }

        return $matches[1];
    }

    protected function printScanResult($keywords, $output)
    {
        $total = count($keywords);
        $keywords = array_values(array_unique($keywords));
        $uniqueTotal = count($keywords);

        $output->writeln("<info>共找到{$total}个语言串，去除重复语言串后，共有{$uniqueTotal}个语言串。</info>");
    }

    protected function scanMenuTrans($dir, $output)
    {
        $keywords = array();

        $path = realpath($this->getRootDir().'/../'.$dir.'/Resources/config');

        if (empty($path)) {
            $output->writeln("<error>不存在{$dir}/Resources/config目录。</error>");
        }

        $finder = new Finder();
        $finder->files()->in($path)->name('menus_*');

        foreach ($finder as $file) {
            $output->write("{$file->getRealpath()}");
            $yaml = new Yaml();
            $menus = $yaml->parse(file_get_contents($file->getRealpath()));
            $names = ArrayToolkit::column($menus, 'name');
            $fullnames = ArrayToolkit::column($menus, 'fullname');
            $menuKeywords = array_merge($names, $fullnames);
            $keywords = array_merge($keywords, $menuKeywords);
            $output->writeln(sprintf('<info> ... %s</info>', count($menuKeywords)));
        }

        return $keywords;
    }

    protected function scanDictTrans($dir, $output)
    {
        $keywords = array();

        $path = realpath($this->getRootDir().'/../'.$dir.'/Extensions');

        if (empty($path)) {
            $output->writeln("<error>不存在{$dir}/Extensions目录。</error>");

            return $keywords;
        }

        $finder = new Finder();
        $finder->files()->in($path)->name('data_dict.yml');

        foreach ($finder as $file) {
            $output->write("{$file->getRealpath()}");
            $yaml = new Yaml();
            $dicts = $yaml->parse(file_get_contents($file->getRealpath()));
            $keywords = $this->arrayReduce($dicts);
            $output->writeln(sprintf('<info> ... %s</info>', count($keywords)));
        }

        return $keywords;
    }

    protected function scanViewTrans($dir, $output)
    {
        $keywords = array();

        $path = realpath($this->getRootDir().'/../'.$dir.'/Resources/views');

        if (empty($path)) {
            $output->write("<error>{$dir}/Resources/views is not exist.</error>");
        }

        $finder = new Finder();
        $finder->files()->in($path);

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());

            $matched = preg_match_all('/\{\{\s*\'(.*?)\'\s*\|\s*?trans.*?\}\}/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");

                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        return $keywords;
    }

    protected function scanViewRadioTrans($dir, $output)
    {
        $keywords = array();

        $path = realpath($this->getRootDir().'/../'.$dir.'/Resources/views');

        if (empty($path)) {
            $output->write("<error>{$dir}/Resources/views is not exist.</error>");
        }

        $finder = new Finder();
        $finder->files()->in($path);

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());

            $matched = preg_match_all('/[\:]\s*\'([^,\{\}]+)\'\s*\|\s*?trans/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");

                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());

            $matched = preg_match_all('/select_options.*\s*\,\s*\'([^,\{\}]+)\'\s*\|\s*?trans/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");

                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());

            $matched = preg_match_all('/form_label.*\s*\,\s*\'([^,\{\}]+)\'\s*\|\s*?trans/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");

                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());

            $matched = preg_match_all('/\?\s*\'([^,\{\}?]+)\'\s*\|\s*?trans\s*\:/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");

                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());

            $matched = preg_match_all('/set[^?{%=]*\=\s*\'([^,\{\}]+)\'\s*\|\s*?trans/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");

                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        return $keywords;
    }

    protected function scanViewDefaultTrans($dir, $output)
    {
        $keywords = array();

        $path = realpath($this->getRootDir().'/../'.$dir.'/Resources/views');

        if (empty($path)) {
            $output->write("<error>{$dir}/Resources/views is not exist.</error>");
        }

        $finder = new Finder();
        $finder->files()->in($path);

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());

            $matched = preg_match_all('/default\(\s*\'([^,\{\}\~]+)\'\s*\|\s*?trans/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");

                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        return $keywords;
    }

    protected function scanJSTrans($dir, $output)
    {
        $keywords = array();

        $path = realpath($this->getRootDir().'/../'.$dir.'/Resources/public/js');

        if (empty($path)) {
            $output->write("<error>{$dir}/Resources/public/js is not exist.</error>");
        }

        $finder = new Finder();
        $finder->files()->in($path);

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());

            $matched = preg_match_all('/trans\(\'(.*?)\'/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");

                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        $path = realpath($this->getRootDir().'/../web/assets/libs/common');
        if (empty($path)) {
            $output->write('<error>js is not exist.</error>');
        }

        $finder = new Finder();
        $finder->files()->in($path);

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());

            $matched = preg_match_all('/trans\(\'(.*?)\'/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");

                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        return $keywords;
    }

    protected function scanPHPTrans($dir, $output)
    {
        $keywords = array();

        $path = array(realpath($this->getRootDir().'/../'.$dir));

        if (!in_array($dir, $this->standardPath())) {
            $path = array_merge($path, $this->additionalPath($dir));
        }

        if (empty($path)) {
            $output->write("<error>{$dir}/Controller is not exist.</error>");

            return $keywords;
            exit;
        }

        $finder = new Finder();
        $finder->files()->in($path)->name('*.php');

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealpath());
            $matched = preg_match_all('/trans\(\'(.*?)\'/', $content, $matches);

            if ($matched) {
                $output->write("{$file->getRealpath()}");
                $count = count($matches[1]);
                $output->writeln("<info> ... {$count}</info>");
                $keywords = array_merge($keywords, $matches[1]);
            }
        }

        return $keywords;
    }

    /*数组降维*/
    private function arrayReduce($dicts)
    {
        $dictText = array();

        foreach ($dicts as $key => $dict) {
            if (is_array($dict)) {
                $dictText = array_merge($dictText, array_values($dict));
            } else {
                $dictText[] = $dict;
            }
        }

        return $dictText;
    }

    private function standardPath()
    {
        return array(
            'src/Topxia/AdminBundle',
            'src/Topxia/MobileBundle',
            'src/Topxia/MobileBundleV2',
        );
    }

    private function additionalPath($dir)
    {
        $additionalPath = array(
            realpath($this->getRootDir().'/../'.$dir.'/../Service'),
            realpath($this->getRootDir().'/../'.$dir.'/../Common'),
            realpath($this->getRootDir().'/../'.$dir.'/../Component'),
        );

        return array_filter($additionalPath, function ($additional) {
            return !is_bool($additional);
        });
    }

    private function getRootDir()
    {
        return $this->getContainer()->getParameter('kernel.root_dir');
    }
}
