<?php

namespace Topxia\WebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class BuildVendorCommand extends ContainerAwareCommand
{

    /**
     * project root dir
     * @var string $rootDir
     */
    public $rootDir;

    /**
     * build dir
     * @var string $buildDir
     */
    public $buildDir;

    public $unneededFiles = array(
        'appveyor.yml',
        'CONTRIBUTING.md',
        'CONTRIBUTORS.md',
        'phpunit',
        '.gitignore',
        '.travis.yml',
        'CHANGELOG.md',
        'composer.json',
        'phpunit.xml.dist',
        'Gemfile',
        'README.md',
        'UPGRADE.md',
        'VERSION',
        'CHANGES',
        '.gitattributes',
        '.DS_Store'
    );

    protected function configure()
    {
        $this
            ->setName('build:vendor')
            ->setDescription('制作vendor ZIP包')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rootDir  = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../vendor');
        $this->buildDir = $this->rootDir . '/../build/vendor';

        $fileSystem = new Filesystem();

        if($fileSystem->exists($this->buildDir)){
            $fileSystem->remove($this->buildDir);
        }

        // copy autoload.php
        $fileSystem->mkdir($this->buildDir);
        $fileSystem->copy($this->rootDir . DIRECTORY_SEPARATOR . 'autoload.php', $this->buildDir . DIRECTORY_SEPARATOR . 'autoload.php');


        // copy vendor
        $finder = new Finder();
        $finder->directories()->in($this->rootDir);
        $targetDirs = array();
        foreach ($finder as $dir){
            $output->writeln(sprintf('<info>copying %s</info>', $dir));
            $vendorDir = substr($dir, strpos($dir, 'vendor') + strlen('vendor' . DIRECTORY_SEPARATOR));
            $targetDir = $this->buildDir . DIRECTORY_SEPARATOR . $vendorDir;
            if($dir->isDir() && $dir->isReadable()){
                $fileSystem->mirror($dir, $targetDir);
                $targetDirs[] = $targetDir;
            }
        }

        // remove unneeded files
        $needRemove = array(
            $this->buildDir . DIRECTORY_SEPARATOR . 'composer/installed.json',
        );
        $finder = new Finder();
        $finder->directories()->in($targetDirs);
        foreach ($finder as $dir){
            $dirName = $dir->getFilename();
            if(lcfirst($dirName) === 'tests'){
                $output->writeln(sprintf('<info>removing unneeded dir %s</info>', $dirName));
                $needRemove[] = $dir->getRealPath();
            }
        }

        $finder = new Finder();
        $finder->files()->ignoreDotFiles(false)->in($targetDirs);
        foreach ($finder as $file){
            if(in_array($file->getFilename(), $this->unneededFiles, true)){
                $output->writeln(sprintf('<info>removing unneeded file %s</info>', $file->getFilename()));
                $needRemove[] = $file->getRealPath();
            }
        }

        $fileSystem->remove($needRemove);
    }
}
