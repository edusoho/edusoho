<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Topxia\System;
use Topxia\Common\BlockToolkit;


class BuildVendorZipCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'topxia:build-vendor-zip' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initBuild($input, $output);
        $this->zero();

        $finder = new Finder();
        $finder->directories()->in("{$this->rootDirectory}/vendor2")->depth('== 0');

        $map = array(
            1 => array('composer', 'ezyang', 'gregwar', 'imagine', 'incenteev', 'jdorn', ),
            2 => array('doctrine', ),
            3 => array('endroid', ),
            4 => array('kriswallsmith', 'monolog', 'phpoffice'),
            5 => array('pimple', 'psr', 'sensio', 'sensiolabs', 'silex', 'swiftmailer', 'symfony/assetic-bundle', 'symfony/monolog-bundle', 'symfony/swiftmailer-bundle', 'twig', 'symfony/symfony/src/Symfony/Bridge', 'symfony/symfony/src/Symfony/Bundle'),
            6 => array('symfony/symfony/src/Symfony/Component/Intl', 'symfony/symfony/src/Symfony/Component/HttpKernel', 'symfony/symfony/src/Symfony/Component/Security', 'symfony/symfony/src/Symfony/Component/Form'),
            7 => array('symfony/symfony/src/Symfony/Component'),
        );

        foreach ($map as    $i => $dirs) {
            $this->filesystem->remove($this->distDirectory);
            $this->filesystem->mkdir($this->distDirectory);
            $this->filesystem->mkdir("{$this->distDirectory}/vendor2");

            foreach ($dirs as $dir) {
                $this->filesystem->mirror("{$this->rootDirectory}/vendor2/{$dir}", "{$this->distDirectory}/vendor2/{$dir}");
                if ($i == 7) {
                    $this->filesystem->remove("{$this->distDirectory}/vendor2/{$dir}/Intl");
                    $this->filesystem->remove("{$this->distDirectory}/vendor2/{$dir}/HttpKernel");
                    $this->filesystem->remove("{$this->distDirectory}/vendor2/{$dir}/Security");
                    $this->filesystem->remove("{$this->distDirectory}/vendor2/{$dir}/Form");
                }
            }

            chdir($this->buildDirectory);

            $command = "zip -r vendor-{$i}.zip edusoho/";
            exec($command);
        }
    }

    private function zero()
    {
        $this->filesystem->mkdir("{$this->distDirectory}/app");
        $this->filesystem->mkdir("{$this->distDirectory}/vendor2");

        $this->filesystem->copy("{$this->rootDirectory}/app/autoload.php", "{$this->distDirectory}/app/autoload.php");
        $this->filesystem->copy("{$this->rootDirectory}/app/bootstrap.php.cache", "{$this->distDirectory}/app/bootstrap.php.cache");
        $this->filesystem->copy("{$this->rootDirectory}/vendor2/autoload.php", "{$this->distDirectory}/vendor2/autoload.php");

        chdir($this->buildDirectory);

        $command = "zip -r vendor-0.zip edusoho/";
        exec($command);
    }

    private function initBuild(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->rootDirectory = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../');
        $this->buildDirectory = $this->rootDirectory . '/build';

        $this->filesystem = new Filesystem();

        if ($this->filesystem->exists($this->buildDirectory)) {
            $this->filesystem->remove($this->buildDirectory);
        }
        $this->distDirectory = $this->buildDirectory . '/edusoho';
        $this->filesystem->mkdir($this->distDirectory);
    }

}