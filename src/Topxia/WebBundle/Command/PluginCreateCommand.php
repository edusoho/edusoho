<?php
namespace Topxia\WebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Sensio\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Filesystem\Filesystem;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Topxia\Common\ArrayToolkit;

class PluginCreateCommand extends GeneratorCommand
{
    protected function configure()
    {
        $this
            ->addArgument(
                'bundlename',
                InputArgument::OPTIONAL,
                '插件名称?'
            )
            ->setName('plugin:create')
            ->setDescription('创建插件模板')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $dialog = $this->getDialogHelper();
        $name = $input->getArgument('bundlename');

        if (!$name) {
            throw new \RuntimeException("插件名称不能为空！");
        }

        if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
            throw new \RuntimeException("插件名称只能为英文！");
        }
        $name=ucfirst($name);

        $bundle=$name."Bundle";
        $namespace=$name;
        $pluginName=$namespace;
        $namespace=$namespace."\\".$name."Bundle";
       
        
        $dir=$this->getContainer()->getParameter('kernel.root_dir')."/..";
        $dir=$dir."/plugins";
        $format="yml";
        $structure="yes";
        $generator = $this->getGenerator();
        $generator->generate($namespace, $bundle, $dir, $format, $structure);

        $output->writeln('创建插件包: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        //write jspn
        $filename=$dir."/".$pluginName."/plugin.json";
        
        $data = 
        '{
            "code": "'.$pluginName.'",
            "name": "'.$pluginName.'",
            "description": "",
            "author": "EduSoho官方",
            "version": "1.0.0",
            "supprot_version": "1.0.0"
        }';

        file_put_contents ($filename, $data);

        //mkdir script
        $this->filesystem = new Filesystem();

        $this->filesystem ->mkdir($dir."/".$pluginName."/Scripts");
        $this->filesystem ->mkdir($dir."/".$pluginName."/Service");

        $this->filesystem ->mkdir($dir."/".$pluginName."/Service/".$pluginName."");

        $this->filesystem ->mkdir($dir."/".$pluginName."/Service/".$pluginName."/Impl");
        $this->filesystem ->mkdir($dir."/".$pluginName."/Service/".$pluginName."/Dao");
        $this->filesystem ->mkdir($dir."/".$pluginName."/Service/".$pluginName."/Dao/Impl");

        $data=$this->getBaseInstallScript();
        file_put_contents ($dir."/".$pluginName."/Scripts/BaseInstallScript.php", $data);

        $data=$this->getInstallScript();
        file_put_contents ($dir."/".$pluginName."/Scripts/InstallScript.php", $data);

        $data=$this->getService($pluginName);
        file_put_contents ($dir."/".$pluginName."/Service/".$pluginName."/".$pluginName."Service.php", $data);

        $data=$this->getServiceImpl($pluginName);
        file_put_contents ($dir."/".$pluginName."/Service/".$pluginName."/Impl/".$pluginName."ServiceImpl.php", $data);

        $data=$this->getDao($pluginName);
        file_put_contents ($dir."/".$pluginName."/Service/".$pluginName."/Dao/".$pluginName."Dao.php", $data);

        $data=$this->getDaoImpl($pluginName);
        file_put_contents ($dir."/".$pluginName."/Service/".$pluginName."/Dao/Impl/".$pluginName."DaoImpl.php", $data);
        $dialog->writeGeneratorSummary($output, $errors);
    }

    private function getData($data,$pluginName)
    {
        return str_replace("{{name}}", $pluginName, $data);
    }

    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'));
    }

    public function getDaoImpl($pluginName)
    {   
        $data=file_get_contents(__DIR__."/plugins-tpl/DaoImpl.twig");

        return $this->getData($data,$pluginName);
    }

    public function getDao($pluginName)
    {
        $data=file_get_contents(__DIR__."/plugins-tpl/Dao.twig");

        return $this->getData($data,$pluginName);
    }   

    public function getServiceImpl($pluginName)
    {
        $data=file_get_contents(__DIR__."/plugins-tpl/ServiceImpl.twig");

        return $this->getData($data,$pluginName);
    }

    public function getService($pluginName)
    {
        $data=file_get_contents(__DIR__."/plugins-tpl/Service.twig");

        return $this->getData($data,$pluginName);
    }

    private function getInstallScript()
    {
        $data=file_get_contents(__DIR__."/plugins-tpl/InstallScript.twig");

        return $this->getData($data,"");
    }

    private function getBaseInstallScript()
    {
        $data=file_get_contents(__DIR__."/plugins-tpl/BaseInstallScript.twig");

        return $this->getData($data,"");
    }
}