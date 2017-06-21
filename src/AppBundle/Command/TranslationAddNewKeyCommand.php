<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class TranslationAddNewKeyCommand extends BaseCommand
{
    private $rootDir;

    protected function configure()
    {
        $this->setName('translation:add')
            ->addArgument('translationFilePath', InputArgument::REQUIRED, 'translationFilePath')
            ->setDescription('add translation into en file');
    }

    private function isYamlKeyVal($line)
    {
        $pattern = '/^([a-zA-Z]+[._]?)+[a-zA-Z]+:/';
        $flag = preg_match($pattern, $line);

        return $flag;
    }

    private function isAnnotationLine($line)
    {
        $pattern = '/^#.+/';
        $flag = preg_match($pattern, $line);

        return $flag;
    }

    private function isEnglish($val)
    {
        return !preg_match("/([\x81-\xfe][\x40-\xfe])/", $val);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));
        $translationFilePath = $input->getArgument('translationFilePath');

        $translationFile = $this->rootDir.'/'.$translationFilePath;
        if (!file_exists($translationFile)) {
            $output->writeln('<error>File Not Exist</error>');
            exit;
        }
        $newTrans = Yaml::parse($translationFile);
        if ($newTrans) {
            $output->writeln('<info>新的翻译,<符合yml格式></符合yml格式></info>');
            $this->addNewTrans($newTrans, $output);

            return;
        }

        $transArray = array();
        $file_handle = fopen($translationFile, 'r');
        $lastKey;
        $lastVal;
        while (!feof($file_handle)) {
            $line = fgets($file_handle);
            $line = trim($line);
            if (!$line) {
                continue;
            }
            if ($this->isAnnotationLine($line)) {
                continue;
            }
            // var_dump($line);
            $flag = $this->isYamlKeyVal($line);
            // var_dump($flag);
            if ($flag) {
                $yamlArray = explode(':', $line, 2);
                $yamlKey = $yamlArray[0];
                $yamlKey = str_replace(' ', '', $yamlKey);
                $yamlVal = $yamlArray[1];
                $yamlVal = trim($yamlVal);
                $transArray[$yamlKey] = $yamlVal;
                $lastKey = $yamlKey;
                $lastVal = $yamlVal;
            } else {
                $lastVal = $lastVal.$line;
                $transArray[$lastKey] = $lastVal;
            }
        }

        // var_dump($transArray);exit();
    }

    private function addNewTrans($transArray, $output)
    {
        $enFile = $this->getEnFile();
        $enTranslations = Yaml::parse($enFile);
        $newEnTrans = array();
        $oldEnTrans = array();

        foreach ($transArray as $key => $translation) {
            if (!empty($enTranslations[$key]) && $this->isEnglish($translation)) {
                $enTranslations[$key] = $translation;
                $oldEnTrans[$key] = $translation;
            } else {
                $newEnTrans[$key] = $translation;
            }
        }

        file_put_contents("{$this->rootDir}/app/Resources/translations/messages.en.yml", Yaml::dump($enTranslations));
        $output->writeln('<info>新的翻译，添加完成</info>');

        file_put_contents("{$this->rootDir}/app/Resources/translations/messages.old_en.yml", Yaml::dump($oldEnTrans));
        $output->writeln('<info>提交的新的key，替换完成</info>');

        file_put_contents("{$this->rootDir}/app/Resources/translations/messages.en_newKey.yml", Yaml::dump($newEnTrans));
        $output->writeln('<info>提交的新的key，生成文件完成</info>');
    }

    private function getEnFile()
    {
        return $this->rootDir.'/app/Resources/translations/messages.en.yml';
    }
}
