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
        $x8PackageDir = $buildDir.'/EduSoho_8.0.0';
        $x8SourceDir = $x8PackageDir.'/source';

        $commandInput = new ArrayInput(array(
            'fromVersion' => '7.5.14',
            'version' => '8.0.0',
        ));

        $command->run($commandInput, $output);

        $filesystem = new Filesystem();

        $filesystem->remove($x8PackageDir.'/delete');
        $filesystem->touch($x8PackageDir.'/delete');

        $zip = new \ZipArchive();
        $zip->open($buildDir.'/x8.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $x8SourceDir = str_replace('\\', '/', realpath($x8SourceDir));
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($x8SourceDir), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                continue;
            }

            $file = realpath($file);

            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($x8SourceDir.'/', '', $file.'/'));
            } elseif (is_file($file) === true) {
                $zip->addFromString(str_replace($x8SourceDir.'/', '', $file), file_get_contents($file));
            }
        }
        $zip->close();

        $filesystem->remove($x8SourceDir);
        $filesystem->mkdir($x8SourceDir);

        $zip->open($buildDir.'/EduSoho_8.0.0.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zip->addEmptyDir('source');
        $zip->addFromString('delete', '');
        $zip->addFromString('Upgrade.php', file_get_contents($x8PackageDir.'/Upgrade.php'));
        $zip->close();
    }
}
