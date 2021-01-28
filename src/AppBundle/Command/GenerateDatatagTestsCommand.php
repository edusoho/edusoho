<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenerateDatatagTestsCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('generate:datatag-tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();
        $directory = realpath(dirname(dirname(__DIR__)).'/DataTag');
        $finder->files()->in($directory)->depth('== 0');
        foreach ($finder as $file) {
            $basename = $file->getBasename('.php');
            if (in_array($basename, array('BaseDataTag', 'DataTag'))) {
                continue;
            }

            if (file_exists("{$directory}/Tests/{$basename}Test.php")) {
                $output->writeln("<comment>[EXIST] </comment>\t{$basename}Test");
            } else {
                $output->writeln("<info>[CREATE]</info>\t{$basename}Test");
                $template = file_get_contents(__DIR__.'/Templates/DatatagTest.twig');
                $content = $this->simpleTemplateFilter($template, array('name' => $basename));
                file_put_contents("{$directory}/Tests/{$basename}Test.php", $content);
            }
        }
    }

    protected function simpleTemplateFilter($text, $variables)
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{'.$key.'}}', $value, $text);
        }

        return $text;
    }
}
