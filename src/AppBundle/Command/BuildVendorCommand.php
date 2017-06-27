<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class BuildVendorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('build:vendor')
            ->setDescription('制作vendor ZIP包')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new Filesystem();
        $finder = new Finder();
        $biz = $this->getContainer()->get('biz');

        $rootDir = $biz['kernel.root_dir'].'/../';
        $originDir = $rootDir.'vendor/';
        $buildVendorDir = $rootDir.'build/edusoho/vendor/';
        $fileSystem->mkdir($buildVendorDir);

        $output->writeln('remove develop bundle using command: composer install --no-dev');
        $this->ignoreDevelopVendor($rootDir, $fileSystem);

        $this->copyVendor($output, $finder, $originDir, $fileSystem, $buildVendorDir);

        $output->writeln('recovery bundle using command: composer install');
        $this->recoveryDevelopVendor($rootDir);

        $this->cleanDevlopVendorFiles($output, $finder, $fileSystem, $buildVendorDir);
        exec('git checkout -- vendor');
    }

    /**
     * we ignore require-dev vendor vae composer.json  by composer install --no-dev
     * and remove file that dependance with SensioGeneratorBundle
     *
     * @param $rootDir
     * @param $fileSystem
     */
    protected function ignoreDevelopVendor($rootDir, $fileSystem)
    {
        chdir($rootDir);
        $fileSystem->remove($rootDir.'vendor/codeages/plugin-bundle/Command/PluginCreateCommand.php');
        exec('composer install --no-dev');
    }

    /**
     * @param OutputInterface $output
     * @param $finder
     * @param $originDir
     * @param $fileSystem
     * @param $buildVendorDir
     */
    protected function copyVendor(OutputInterface $output, Finder $finder, $originDir, Filesystem $fileSystem, $buildVendorDir)
    {
        $finder->depth('== 1')->in($originDir);
        foreach ($finder as $folder) {
            $fileName = $folder->getFilename();
            if ($folder->isFile()) {
                $paths = explode('vendor', $folder->getRealPath());
                $fileSystem->copy($folder->getRealPath(), $buildVendorDir.$paths[1]);
                $output->writeln('build vendor'.$paths[1]);
            } else {
                $targetDir = $buildVendorDir.$folder->getRelativePath().'/'.$fileName;
                $fileSystem->mirror($folder->getRealPath(), $targetDir);
                $output->writeln('build vendor/'.$folder->getRelativePath().'/'.$fileName);
            }
        }
        $fileSystem->copy($originDir.'autoload.php', $buildVendorDir.'autoload.php');
    }

    protected function recoveryDevelopVendor($rootDir)
    {
        chdir($rootDir);
        exec('git checkout -- composer.lock');
        exec('composer install');
        exec('git checkout -- vendor');
    }

    protected function ignoreVendorFiles()
    {
        return  array(
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
            '.DS_Store',
            'CHANGELOG',
            'changelog.txt',
            'README',
            'README_zh_CN.md',
        );
    }

    /**
     * remvove test file and document
     *
     * @param OutputInterface $output
     * @param $finder
     * @param $fileSystem
     */
    protected function cleanDevlopVendorFiles(OutputInterface $output, Finder $finder, Filesystem $fileSystem, $buildVendorDir)
    {
        $finder->in($buildVendorDir)->depth('<= 3')->ignoreUnreadableDirs(true);
        foreach ($finder as $folder) {
            if (in_array($folder->getFilename(), array('tests', 'Tests', 'test'))) {
                $output->writeln('remove  Test folder : '.$folder->getRelativePath().'/'.$folder->getFilename());
                $fileSystem->remove($folder->getRealPath());
            }
            if ($folder->isFile() && in_array($folder->getFilename(), $this->ignoreVendorFiles())) {
                $output->writeln('remove  File : '.$folder->getRelativePath().'/'.$folder->getFilename());
                $fileSystem->remove($folder->getRealPath());
            }
        }
    }
}
