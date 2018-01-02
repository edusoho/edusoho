<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\FInder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

class TranslationJsDumperCommand extends BaseCommand
{
    private $filePath = array(
        'main' => 'web/static-dist/translations/',
        'plugin' => 'plugin/'
    );
    protected function configure()
    {
        $this->setName('trans:dump-js')
            ->addOption(
                'code',
                null,
                InputOption::VALUE_REQUIRED,
                '',
                ''
            )
            ->setDescription('生成静态js翻译文件');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getOption('code', '');
        $output->writeln('<info>开始生出js翻译文件</info>');
        $this->dump($code);
        $output->writeln('<info>成功</info>');
    }

    private function dump($code)
    {
        $filesystem = new Filesystem();
        $translations = $this->getTranslations($code);
        foreach($translations as $locale => $translation) {
            if (empty($translation)) {
                continue;
            }
            $template = $this->getContainer()->get('templating');
            $content = $template->render('BazingaJsTranslationBundle::getTranslations.js.twig', array(
                'translations'   => array($locale => array(
                    'js' => $translation,
                )),
                'include_config' => false,
            ));
            $filePath = empty($code) ? 'web/static-dist/translations/'
            : 'plugins/'.ucfirst($code).'Plugin/Resources/static-dist/js/translations/';
            $file = $filePath . $locale . '.js' ;
            var_dump( $file);
            $filesystem->mkdir(dirname($file));

            if (file_exists($file)) {
                $filesystem->remove($file);
            }

            file_put_contents($file, $content);
        }
    }

    private function getTranslations($code)
    {
        $translations = array();
        $rootDirectory = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../');

        $yaml = new Yaml();
        $finder = $this->getContainer()->get('bazinga.jstranslation.translation_finder');
        $files = $finder->all();
        $filePath = 'web/static-dist/translations/';

        foreach ($files as $filename) {
            list($domain, $locale, $extension) = $this->getFileInfo($filename);
            if ('js' != $domain || 'yml' != $extension) {
                continue;
            }

            if(!empty($code) && 0 !== strpos($filename, $rootDirectory.'/plugins/'.ucfirst($code).'Plugin') ){
                continue;
            }
            
            if (empty($code) && 0 === strpos($filename, $rootDirectory.'/plugins/')) {
                 continue;
            }
 
            if (!isset($translations[$locale])) {
                $translations[$locale] = array();
            }

            $translations[$locale] = array_merge($translations[$locale], $yaml->parse($filename));
        }

        return $translations;
    }

    private function getFileInfo($filename)
    {
        list($domain, $locale, $extension) = explode('.', basename($filename), 3);

        return array($domain, $locale, $extension);
    }
}