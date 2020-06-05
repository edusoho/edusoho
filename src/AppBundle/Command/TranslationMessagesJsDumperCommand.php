<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class TranslationMessagesJsDumperCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('trans:dump-js-messages')
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
        $output->writeln('<info>开始生成js翻译文件</info>');
        $this->dump($code);
        $output->writeln('<info>成功</info>');
    }

    private function dump($code)
    {
        $filesystem = new Filesystem();
        $translations = $this->getTranslations($code);
        foreach ($translations as $locale => $translation) {
            if (empty($translation)) {
                continue;
            }
            $template = $this->getContainer()->get('templating');
            $content = $template->render('BazingaJsTranslationBundle::getTranslations.js.twig', [
                'translations' => [$locale => [
                    'js' => $translation,
                ]],
                'include_config' => true,
                'fallback' => $locale,
                'defaultDomain' => 'js',
            ]);

            if (empty($code)) {
                $filePaths = ['web/translations/messages/'];
            } else {
                $filePaths = [
                    'plugins/'.ucfirst($code).'Plugin/Resources/public/js/controller/translations/',
                    'plugins/'.ucfirst($code).'Plugin/Resources/static-src/js/translations/',
                ];
            }
            foreach ($filePaths as $filePath) {
                $file = $filePath.$locale.'.js';
                $filesystem->mkdir(dirname($file));

                if (file_exists($file)) {
                    $filesystem->remove($file);
                }

                file_put_contents($file, $content);
            }
        }
    }

    private function getTranslations($code)
    {
        $translations = [];
        $rootDirectory = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../');

        $yaml = new Yaml();
        $finder = $this->getContainer()->get('bazinga.jstranslation.translation_finder');
        $files = $finder->all();

        foreach ($files as $filename) {
            list($domain, $locale, $extension) = $this->getFileInfo($filename);
            if ('messages' != $domain || 'yml' != $extension) {
                continue;
            }

            if (!empty($code) && 0 !== strpos(realpath($filename), $rootDirectory.'/plugins/'.ucfirst($code).'Plugin')) {
                continue;
            }

            if (empty($code) && 0 === strpos(realpath($filename), $rootDirectory.'/plugins/')) {
                continue;
            }

            if (!isset($translations[$locale])) {
                $translations[$locale] = [];
            }

            $fileContent = $yaml->parse(file_get_contents($filename));

            if (!empty($fileContent)) {
                $translations[$locale] = array_merge($translations[$locale], $fileContent);
            }
        }

        return $translations;
    }

    private function getFileInfo($filename)
    {
        list($domain, $locale, $extension) = explode('.', basename($filename), 3);

        return [$domain, $locale, $extension];
    }
}
