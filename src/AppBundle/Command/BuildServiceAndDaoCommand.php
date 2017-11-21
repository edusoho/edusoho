<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class BuildServiceAndDaoCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('BuildServiceAndDao')
            ->setDescription('快速创建Service，Dao')
            ->addArgument('name', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $biz = $this->getBiz();
        $name = $input->getArgument('name');
        $output->writeln('开始创建');
        $rootDir = $biz['root_directory'];
        $targetDir = $rootDir.'src/Biz/'.$name;
        $fileSystem = new Filesystem();

        $this->mkdirTargetDir($fileSystem,$targetDir);
        $this->copeFiles($fileSystem, $targetDir, $rootDir);
        $this->replaceKey($fileSystem, $targetDir, $name);

        $output->writeln('创建成功');
    }

    private function mkdirTargetDir($fileSystem, $targetDir)
    {
        $fileSystem->mkdir($targetDir);
    }

    private function copeFiles($fileSystem, $targetDir, $rootDir)
    {
        $fileSystem->mirror($rootDir.'src/AppBundle/Command/NewTemplates/ServiceAndDaoDemo/', $targetDir, null, array('override' => true, 'delete' => true));
    }

    private function replaceKey($fileSystem, $targetDir, $name)
    {
        $addresses = array(
            'Service/Service' => "Service/{$name}Service.php",
            'Service/Impl/ServiceImpl' => "Service/Impl/{$name}ServiceImpl.php",
            'Dao/Dao' => "Dao/{$name}Dao.php",
            'Dao/Impl/DaoImpl' => "Dao/Impl/{$name}DaoImpl.php",
        );

        foreach($addresses as $key => $address) {
            $data = file_get_contents($targetDir.'/'.$key);
            $data = str_replace('{{name}}', $name, $data);
            $fileSystem->remove($targetDir.'/'.$key);
            file_put_contents($targetDir.'/'.$address, $data);
        }
    }

}
