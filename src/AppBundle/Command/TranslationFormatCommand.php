<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class TranslationFormatCommand extends BaseCommand
{
    private $rootDir;

    protected function configure()
    {
        $this->setName('translation:format')
            ->addArgument('transFile', InputArgument::REQUIRED, 'transFile')
            ->addArgument('format', InputArgument::OPTIONAL, 'format trans file', true)
            ->addArgument('appendZhKeyToEn', InputArgument::OPTIONAL, 'append zh  trans key to en file', false)
            ->setDescription('translation:format');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));
        $transFile = $input->getArgument('transFile');
        $appendZhKeyToEn = $input->getArgument('appendZhKeyToEn');
        $format = $input->getArgument('format');

        $translationFile = $this->rootDir.'/app/Resources/translations/'.$transFile;
        if (!file_exists($translationFile)) {
            $output->writeln('<error>File Not Exist</error>');
            exit;
        }
        $trans = Yaml::parse($translationFile);

        if ($format === 'true') {
            file_put_contents($translationFile, Yaml::dump($trans));
            $output->writeln('<info>文件格式化完成</info>');
        }

        if ($appendZhKeyToEn === 'true') {
            $this->appendZhKeyToEn($translationFile);
            $output->writeln('<info>添加中文国际化到英文文件完成</info>');
        }
    }

    private function appendZhKeyToEn($translationFile)
    {
        $zhFile = $this->rootDir.'/app/Resources/translations/messages.zh_CN.yml';
        $enFile = $translationFile;
        $zhTrans = Yaml::parse($zhFile);
        $enTrans = Yaml::parse($enFile);

        $zhExcessKey = array_diff_key($zhTrans, $enTrans);
        $enExcessKey = array_diff_key($enTrans, $zhTrans);

        file_put_contents("{$this->rootDir}/app/Resources/translations/messages.zh_excess.yml", Yaml::dump($zhExcessKey));
        file_put_contents("{$this->rootDir}/app/Resources/translations/messages.en_excess.yml", Yaml::dump($enExcessKey));

        $mergeEnTrans = array_merge($enTrans, $zhExcessKey);
        file_put_contents("{$this->rootDir}/app/Resources/translations/messages.en.yml", Yaml::dump($mergeEnTrans));
    }
}
