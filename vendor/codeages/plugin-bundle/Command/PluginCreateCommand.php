<?php

namespace Codeages\PluginBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Sensio\Bundle\GeneratorBundle\Model\Bundle;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator;

class PluginCreateCommand extends GeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('plugin:create')
            ->addArgument('code', InputArgument::REQUIRED, 'Plugin code.')
            ->setDescription('Create plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $biz = $this->getContainer()->get('biz');
        $code = $input->getArgument('code');

        if (!$code) {
            throw new \RuntimeException('Plugin code can not be null');
        }

        if (!preg_match('/^[a-zA-Z\s]+$/', $code)) {
            throw new \RuntimeException('Plugin code should be English');
        }

        $name = ucfirst($code);
        $pluginName = $name.'Plugin';
        $rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));

        $output->writeln(sprintf('Create plugin <comment>%s</comment> :', $name));

        $generator = $this->getGenerator();
        $bundleObject = $this->createBundleObject($name);
        $generator->generateBundle($bundleObject);

        $errors = array();
        $this->getQuestionHelper()->getRunner($output, $errors);

        $dir = $bundleObject->getTargetDirectory();

        //write jspn
        $filename = $dir.'/plugin.json';

        $data = '{
            "code": "'.$name.'",
            "name": "'.$name.'",
            "description": "",
            "author": "EduSoho官方",
            "version": "1.0.0",
            "support_version": "8.0.0"
        }';

        file_put_contents($filename, $data);

        //mkdir script
        $this->filesystem = new Filesystem();

        $this->filesystem->mkdir($dir.'/Scripts');
        $this->filesystem->mkdir($dir.'/Migrations');
        $this->filesystem->mkdir($dir.'/Biz');
        $this->filesystem->mkdir($dir.'/Slot');

        $this->filesystem->mkdir($dir.'/Biz/'.$name.'');
        $this->filesystem->mkdir($dir.'/Biz/'.$name.'/Service');
        $this->filesystem->mkdir($dir.'/Biz/'.$name.'/Service/Impl');
        $this->filesystem->mkdir($dir.'/Biz/'.$name.'/Dao');
        $this->filesystem->mkdir($dir.'/Biz/'.$name.'/Dao/Impl');

        $this->filesystem->mkdir($dir.'/Resources/static-src');
        $this->filesystem->mkdir($dir.'/Resources/static-src/js');
        $this->filesystem->mkdir($dir.'/Resources/static-src/img');
        $this->filesystem->touch($dir.'/Resources/config/slots.yml');

        $tplDir = dirname(__FILE__);

        $data = $this->getBaseInstallScript($tplDir);
        file_put_contents($dir.'/Scripts/BaseInstallScript.php', $data);

        $data = $this->getInstallScript($tplDir);
        file_put_contents($dir.'/Scripts/InstallScript.php', $data);

        $data = $this->getService($tplDir, $name);
        file_put_contents($dir.'/Biz/'.$name.'/Service/'.$name.'Service.php', $data);

        $data = $this->getServiceImpl($tplDir, $name);
        file_put_contents($dir.'/Biz/'.$name.'/Service/Impl/'.$name.'ServiceImpl.php', $data);

        $data = $this->getDao($tplDir, $name);
        file_put_contents($dir.'/Biz/'.$name.'/Dao/'.$name.'Dao.php', $data);

        $data = $this->getDaoImpl($tplDir, $name);
        file_put_contents($dir.'/Biz/'.$name.'/Dao/Impl/'.$name.'DaoImpl.php', $data);

        $data = $this->getPlugin($tplDir, $name);
        file_put_contents($dir.'/'.$name.'Plugin.php', $data);

        if (file_exists($dir.'/DependencyInjection/'.$name.'Extension.php')) {
            $this->filesystem->remove($dir.'/DependencyInjection/'.$name.'Extension.php');
            $data = $this->getPluginExtension($tplDir, $name);
            file_put_contents($dir.'/DependencyInjection/'.$name.'PluginExtension.php', $data);
        }

        $output->writeln("<info>Finished!</info>\n");
    }

    /**
     * @param $name
     *
     * @return Bundle
     */
    protected function createBundleObject($name)
    {
        $bundle = $name.'Plugin';
        $namespace = $name.'Plugin';

        $dir = dirname($this->getContainer()->getParameter('kernel.root_dir'));
        $dir = $dir.'/plugins';
        $format = 'yml';

        return new Bundle(
            $namespace,
            $bundle,
            $dir,
            $format,
            $shared = true
        );
    }

    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'));
    }

    private function getData($data, $pluginName)
    {
        return str_replace('{{name}}', $pluginName, $data);
    }

    public function getDaoImpl($tplDir, $pluginName)
    {
        $data = file_get_contents($tplDir.'/plugins-tpl/DaoImpl.twig');

        return $this->getData($data, $pluginName);
    }

    public function getDao($tplDir, $pluginName)
    {
        $data = file_get_contents($tplDir.'/plugins-tpl/Dao.twig');

        return $this->getData($data, $pluginName);
    }

    public function getServiceImpl($tplDir, $pluginName)
    {
        $data = file_get_contents($tplDir.'/plugins-tpl/ServiceImpl.twig');

        return $this->getData($data, $pluginName);
    }

    public function getService($tplDir, $pluginName)
    {
        $data = file_get_contents($tplDir.'/plugins-tpl/Service.twig');

        return $this->getData($data, $pluginName);
    }

    private function getInstallScript($tplDir)
    {
        $data = file_get_contents($tplDir.'/plugins-tpl/InstallScript.twig');

        return $this->getData($data, '');
    }

    private function getBaseInstallScript($tplDir)
    {
        $data = file_get_contents($tplDir.'/plugins-tpl/BaseInstallScript.twig');

        return $this->getData($data, '');
    }

    public function getPlugin($tplDir, $pluginName)
    {
        $data = file_get_contents($tplDir.'/plugins-tpl/Plugin.twig');

        return $this->getData($data, $pluginName);
    }

    public function getPluginExtension($tplDir, $pluginName)
    {
        $data = file_get_contents($tplDir.'/plugins-tpl/PluginExtension.twig');

        return $this->getData($data, $pluginName);
    }
}
