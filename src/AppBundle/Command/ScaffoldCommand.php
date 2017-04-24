<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

class ScaffoldCommand extends BaseCommand
{
    private $name;
    private $sfMode;
    private $service;
    private $srcdir;
    private $twigDir;
    private $serviceDir;
    private $results = array();

    protected function configure()
    {
        $this
            ->setName('topxia:scaffold')
            ->setDescription('创建脚手架')
            ->addArgument('name', InputArgument::REQUIRED, 'entity name')
            ->addArgument('service', InputArgument::REQUIRED, 'which service to locate')
            ->addArgument('sfMode', InputArgument::REQUIRED, 'DSC: d=dao,S=service,C=Controller');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>脚手架生成</info>');
        $this->initBuild($input, $output);

        if (!(strpos($this->sfMode, 'D') === false)) {
            $output->writeln('<info>生成DAO & Impl.</info>');
            $this->results[] = $this->createDaoTemplate();
            $this->results[] = $this->createDaoImplTemplate();
        }
        if (!(strpos($this->sfMode, 'S') === false)) {
            $output->writeln('<info>生成Service & Impl.</info>');
            $this->results[] = $this->createServiceTemplate();
            $this->results[] = $this->createServiceImplTemplate();
        }
        if (!(strpos($this->sfMode, 'C') === false)) {
            $output->writeln('<info>生成后台Controller</info>');
            $this->results[] = $this->createAdminControllerTemplate();
        }

        $output->writeln('<info>End build.</info>');
    }

    private function getInputs()
    {
        return  array('service' => $this->service,
                      'smallName' => lcfirst($this->name),
                      'bigName' => ucfirst($this->name),
        );
    }

    private function createAdminControllerTemplate()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(array($this->twigDir)));
        $rendered = $twig->render(
            'AdminController.twig',
            $this->getInputs()
        );
        $filesystem = new Filesystem();
        $admincontrollerPath = $this->srcdir.'/Topxia/AdminBundle/Controller';
        $filePath = $admincontrollerPath.'/'.ucfirst($this->name).'Controller.php';
        $filesystem->dumpFile($filePath, $rendered, 0777);

        return $filePath;
    }

    private function createServiceTemplate()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(array($this->twigDir)));
        $rendered = $twig->render(
            'Service.twig',
            $this->getInputs()
        );
        $filesystem = new Filesystem();
        $servicePath = $this->serviceDir.'/'.ucfirst($this->service);
        $filesystem->mkdir($servicePath, 0777);
        $filePath = $servicePath.'/'.ucfirst($this->name).'Service.php';
        $filesystem->dumpFile($filePath, $rendered, 0777);

        return $filePath;
    }

    private function createServiceImplTemplate()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(array($this->twigDir)));
        $rendered = $twig->render(
            'ServiceImpl.twig',
            $this->getInputs()
        );
        $filesystem = new Filesystem();
        $servicePath = $this->serviceDir.'/'.ucfirst($this->service).'/Impl';
        $filesystem->mkdir($servicePath, 0777);
        $filePath = $servicePath.'/'.ucfirst($this->name).'ServiceImpl.php';
        $filesystem->dumpFile($filePath, $rendered, 0777);

        return $filePath;
    }

    private function createDaoTemplate()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(array($this->twigDir)));
        $rendered = $twig->render(
            'Dao.twig',
            $this->getInputs()
        );
        $filesystem = new Filesystem();
        $daoPath = $this->serviceDir.'/'.ucfirst($this->service).'/Dao';
        $filesystem->mkdir($daoPath, 0777);
        $filePath = $daoPath.'/'.ucfirst($this->name).'Dao.php';
        $filesystem->dumpFile($filePath, $rendered, 0777);

        return $filePath;
    }

    private function createDaoImplTemplate()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(array($this->twigDir)));
        $rendered = $twig->render(
            'DaoImpl.twig',
            $this->getInputs()
        );
        $filesystem = new Filesystem();
        $daoPath = $this->serviceDir.'/'.ucfirst($this->service).'/Dao/Impl';
        $filesystem->mkdir($daoPath, 0777);
        $filePath = $daoPath.'/'.ucfirst($this->name).'DaoImpl.php';
        $filesystem->dumpFile($filePath, $rendered, 0777);

        return $filePath;
    }

    private function initBuild(InputInterface $input, OutputInterface $output)
    {
        $rootDirectory = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../');
        $this->srcdir = $rootDirectory.'/src';
        $this->twigDir = $this->srcdir.'/Topxia/WebBundle/Command/Templates';
        $this->serviceDir = $this->srcdir.'/Topxia/Service';
        $this->name = $input->getArgument('name');
        $this->service = $input->getArgument('service');
        $this->sfMode = $input->getArgument('sfMode');
    }
}
