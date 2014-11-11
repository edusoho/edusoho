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

class PluginBuildCommand extends GeneratorCommand
{
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('bundlename', '', InputOption::VALUE_REQUIRED, '插件名称'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, '保存路径'),
                )
            )
            ->setName('plugin:build')
            ->setDescription('创建插件模板')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, '欢迎使用插件包脚本');

        // namespace
        $namespace = null;
        try {
            $namespace = $input->getOption('bundlename') ? Validators::validateBundleNamespace($input->getOption('bundlename')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $namespace) {
            $output->writeln(array(
                '',
            ));

            $namespace = $dialog->askAndValidate($output, $dialog->getQuestion('Bundle name', $input->getOption('bundlename')),  array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateBundleName'), false, $input->getOption('bundlename'));
            $input->setOption('bundlename', $namespace);
        }


        // target dir
        $dir = null;
        try {
            $dir = $input->getOption('dir') ? Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $dir) {
            $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/plugins';

            $output->writeln(array(
                '',
            ));
            $dir = $dialog->askAndValidate($output, $dialog->getQuestion('存放路径', $dir), function ($dir) use ($bundle, $namespace) { return Validators::validateTargetDir($dir, $bundle, $namespace); }, false, $dir);
            $input->setOption('dir', $dir);
        }
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $dialog = $this->getDialogHelper();
        $name = $input->getOption('bundlename');

        if (!$name) {
            throw new \RuntimeException("插件名称不能为空！");
        }

        

        $bundle=$name;
        $namespace=str_replace("Bundle","",$name);
        $pluginName=$namespace;
        $namespace=$namespace."\\".$name;
       
        
        $dir=$input->getOption('dir');
        $format="yml";
        $structure="yes";
        $generator = $this->getGenerator();
        $generator->generate($namespace, $bundle, $dir, $format, $structure);

        $output->writeln('Generating the bundle code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        //write jspn
        $filename=$dir.$pluginName."/plugin.json";
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

        $this->filesystem ->mkdir($dir.$pluginName."/Scripts");
        $this->filesystem ->mkdir($dir.$pluginName."/Service");

        $this->filesystem ->mkdir($dir.$pluginName."/Service/Demo");

        $this->filesystem ->mkdir($dir.$pluginName."/Service/Demo/Impl");
        $this->filesystem ->mkdir($dir.$pluginName."/Service/Demo/Dao");
        $this->filesystem ->mkdir($dir.$pluginName."/Service/Demo/Dao/Impl");

        $data=$this->getBaseInstallScript();
        file_put_contents ($dir.$pluginName."/Scripts/BaseInstallScript.php", $data);

        $data=$this->getInstallScript();
        file_put_contents ($dir.$pluginName."/Scripts/InstallScript.php", $data);

        $data=$this->getService($pluginName);
        file_put_contents ($dir.$pluginName."/Service/Demo/DemoService.php", $data);

        $data=$this->getServiceImpl($pluginName);
        file_put_contents ($dir.$pluginName."/Service/Demo/Impl/DemoServiceImpl.php", $data);

        $data=$this->getDao($pluginName);
        file_put_contents ($dir.$pluginName."/Service/Demo/Dao/DemoDao.php", $data);

        $data=$this->getDaoImpl($pluginName);
        file_put_contents ($dir.$pluginName."/Service/Demo/Dao/Impl/DemoDaoImpl.php", $data);
        $dialog->writeGeneratorSummary($output, $errors);
    }


    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'));
    }

    public function getDaoImpl($pluginName)
    {
        return '<?php

namespace '.$pluginName.'\Service\Demo\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use '.$pluginName.'\Service\Demo\Dao\DemoDao;

class DemoDaoImpl extends BaseDao implements DemoDao
{

}';
    }

    public function getDao($pluginName)
    {
        return '<?php

namespace '.$pluginName.'\Service\Demo\Dao;

interface DemoDao
{

}';
    }   

    public function getServiceImpl($pluginName)
    {
        return '<?php
namespace '.$pluginName.'\Service\Demo\Impl;

use Topxia\Service\Common\BaseService;
use '.$pluginName.'\Service\Demo\DemoService;

class DemoServiceImpl extends BaseService implements DemoService
{
    protected function getDemoDao()
    {
        return $this->createDao(\''.$pluginName.':Demo.DemoDao\');
    }
}';
    }

    public function getService($pluginName)
    {
        return '<?php
namespace '.$pluginName.'\Service\Demo;

interface DemoService
{

}';
    }

    private function getInstallScript()
    {
        return '<?php

include_once __DIR__ . \'/BaseInstallScript.php\';

class InstallScript extends BaseInstallScript
{

    public function install()
    {

        $connection = $this->getConnection();
        /* create you database table 
        $connection->exec("DROP TABLE IF EXISTS `cash_orders_log`;");
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `cash_orders_log` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `orderId` int(10) unsigned NOT NULL,
            `message` text,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");*/
    }

}';
    }

    private function getBaseInstallScript()
    {
        return '<?php

use Symfony\Component\Filesystem\Filesystem;

abstract class BaseInstallScript
{

    protected $meta;

    protected $kernel;

    protected $installMode = \'appstore\';

    public function __construct ($kernel)
    {
        $this->kernel = $kernel;
        $this->meta = json_decode(file_get_contents(__DIR__  . \'/../plugin.json\'), true);
    }

    abstract public function install();

    public function execute()
    {
        $this->getConnection()->beginTransaction();
        try{
            $this->install();
            $this->installAssets();
            $this->getConnection()->commit();
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }
    }

    public function setInstallMode($mode)
    {
        if (!in_array($mode, array(\'appstore\', \'command\'))) {
            throw new \RuntimeException("$mode is not validate install mode.");
        }

        $this->installMode = $mode;
    }

    protected function installAssets()
    {
        $code = $this->meta[\'code\'];

        $rootDir = realpath($this->kernel->getParameter(\'kernel.root_dir\') . \'/../\');
        
        $originDir = "{$rootDir}/plugins/{$code}/{$code}Bundle/Resources/public";
        if (!is_dir($originDir)) {
            return false;
        }

        $targetDir = "{$rootDir}/web/bundles/" . strtolower($code);

        $filesystem = new Filesystem();
        if ($filesystem->exists($targetDir)) {
            $filesystem->remove($targetDir);
        }

        if ($this->installMode == \'command\') {
            $filesystem->symlink($originDir, $targetDir, true);
        } else {
            $filesystem->mirror($originDir, $targetDir, null, array(\'override\' => true, \'delete\' => true));
        }
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    protected function getConnection()
    {
        return $this->kernel->getConnection();
    }

}';
    }
}