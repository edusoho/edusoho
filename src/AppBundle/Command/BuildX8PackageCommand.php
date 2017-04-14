<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class BuildX8PackageCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('build:x8-package')
            ->setDescription('编制8.0.0升级包');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('build:upgrade-package');

        $buildDir = $this->getContainer()->get('kernel')->getRootDir().'/../build';

        $commandInput = new ArrayInput(array(
            'fromVersion' => '7.5.14',
            'version' => '8.0.0',
        ));

        $command->run($commandInput, $output);

        $filesystem = new Filesystem();

        $x8PackageDir = $buildDir.'/EduSoho_8.0.0';
        $x8SourceDir = $x8PackageDir.'/source';

        $filesystem->remove($x8PackageDir.'/delete');

        $x8UpgradeDir = $x8SourceDir.'/scripts/8.0.0';
        $x8TempUpgradeDir = $buildDir.'/scripts/8.0.0';

        $filesystem->mirror($x8UpgradeDir, $x8TempUpgradeDir);
        $filesystem->remove($x8UpgradeDir.'/..');

        $filesystem->remove($buildDir.'/x8.zip');

        chdir($this->getContainer()->get('kernel')->getRootDir().'/../');

        exec('rm -rf ./build/EduSoho_8.0.0/source/vendor');
        exec('rm -rf ./build/EduSoho_8.0.0/source/web/static-dist');

        exec('cp -r ./vendor ./build/EduSoho_8.0.0/source');
        exec('mkdir ./build/EduSoho_8.0.0/source/web/static-dist');
        exec('cp -r ./web/static-dist/app ./build/EduSoho_8.0.0/source/web/static-dist');
        exec('cp -r ./web/static-dist/autumntheme ./build/EduSoho_8.0.0/source/web/static-dist');
        exec('cp -r ./web/static-dist/defaulttheme ./build/EduSoho_8.0.0/source/web/static-dist');
        exec('cp -r ./web/static-dist/defaultbtheme ./build/EduSoho_8.0.0/source/web/static-dist');
        exec('cp -r ./web/static-dist/jianmotheme ./build/EduSoho_8.0.0/source/web/static-dist');
        exec('cp -r ./web/static-dist/libs ./build/EduSoho_8.0.0/source/web/static-dist');

        chdir($x8PackageDir);
        $command = 'zip -r ./../x8.zip source/';
        exec($command);

        $filesystem->remove($x8SourceDir);
        $filesystem->mirror($x8TempUpgradeDir, $x8UpgradeDir);

        $filesystem->remove($x8TempUpgradeDir);

        $filesystem->remove($buildDir.'/EduSoho_8.0.0.zip');

        chdir($this->getContainer()->get('kernel')->getRootDir().'/../build');
        $command = 'zip -r EduSoho_8.0.0.zip EduSoho_8.0.0/';
        exec($command);
    }
}
