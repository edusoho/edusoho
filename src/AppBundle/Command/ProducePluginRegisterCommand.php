<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Codeages\PluginBundle\System\PluginRegister;
use AppBundle\Common\BlockToolkit;
use Biz\Util\PluginUtil;
use Symfony\Component\Filesystem\Filesystem;

class ProducePluginRegisterCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('produce_plugin:register')
            ->addArgument('code', InputArgument::REQUIRED, 'Plugin code.')
            ->setDescription('Register plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tmp = sys_get_temp_dir();
        $url = 'http://demo.edusoho.com/abc.zip';

        $biz = $this->getContainer()->get('biz');
        $code = $input->getArgument('code');

        $output->writeln(sprintf('Register plugin <comment>%s</comment> :', $code));

        $rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));

        $installer = new PluginRegister($rootDir, 'plugins', $biz);
        //下载
        $fileName = $this->download($url, $tmp, $code);
        $pluginDir = $installer->getPluginDirectory($code);

        $this->unzipPackageFile($fileName, $pluginDir);
        $pluginRoot = $rootDir.DIRECTORY_SEPARATOR.'plugins';

        $this->_replaceFileForPackageUpdate($pluginDir, $pluginRoot);

        $output->write('  - Parse meta file plugin.json');
        $metas = $installer->parseMetas($code);
        $output->writeln('  <info>[Ok]</info>');

        $output->write('  - Execute create database scripts.');
        $executed = $installer->executeDatabaseScript($code);
        $output->writeln($executed ? '  <info>[Ok]</info>' : '  <info>[Ignore]</info>');

        $output->write('  - Execute install script.');
        $executed = $installer->executeScript($code);
        $output->writeln($executed ? '  <info>[Ok]</info>' : '  <info>[Ignore]</info>');

        $output->write('  - Install block.');
        BlockToolkit::init($installer->getPluginDirectory($code).'/block.json', $this->getContainer());
        $output->writeln('  <info>[Ok]</info>');

        $output->write('  - register plugin to app.');
        $this->updateAppForPackageUpdate($metas);

        $output->write('  - Create plugin installed record.');
        PluginUtil::refresh();
        $output->writeln($executed ? '  <info>[Ok]</info>' : '  <info>[Ignore]</info>');

        $output->write('  - Refresh plugin cache.');
        $this->deleteCache();

        $output->writeln("<info>Finished!</info>\n");
    }

    protected function updateAppForPackageUpdate($metas)
    {
        $newApp = array(
            'code' => $metas['code'],
            'name' => $metas['name'],
            'description' => $metas['description'],
            'version' => $metas['version'],
            'icon' => '',
            'developerName' => $metas['author'],
            'edusohoMinVersion' => $metas['support_version'],
            'protocol' => $metas['protocol'],
            'updatedTime' => time(),
        );

        $app = $this->getAppDao()->getAppByCode($metas['code']);

        if (empty($app)) {
            $newApp['installedTime'] = time();

            return $this->getAppDao()->addApp($newApp);
        }

        return $this->getAppDao()->updateApp($app['id'], $newApp);
    }

    protected function deleteCache($tryCount = 0)
    {
        if ($tryCount >= 5) {
            throw $this->createServiceException('cannot delete cache.');
        }

        sleep($tryCount * 2);

        try {
            $cachePath = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/cache';
            $filesystem = new Filesystem();
            $filesystem->remove($cachePath);
            clearstatcache(true);
            sleep(3);
            //注解需要该目录存在
            if (!$filesystem->exists($cachePath.'/annotations/topxia')) {
                $filesystem->mkdir($cachePath.'/annotations/topxia');
            }
        } catch (\Exception $e) {
            ++$tryCount;
            $this->deleteCache($tryCount);
        }
    }

    protected function _replaceFileForPackageUpdate($package, $pluginDir)
    {
        $filesystem = new Filesystem();
        $filesystem->mirror("{$package}/source", $pluginDir, null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));
    }

    protected function unzipPackageFile($filepath, $unzipDir)
    {
        $filesystem = new Filesystem();

        if ($filesystem->exists($unzipDir)) {
            $filesystem->remove($unzipDir);
        }

        $tmpUnzipDir = $unzipDir.'_tmp';

        if ($filesystem->exists($tmpUnzipDir)) {
            $filesystem->remove($tmpUnzipDir);
        }

        $filesystem->mkdir($tmpUnzipDir);

        $zip = new \ZipArchive();

        if ($zip->open($filepath) === true) {
            $tmpUnzipFullDir = $tmpUnzipDir.'/'.$zip->getNameIndex(0);
            $zip->extractTo($tmpUnzipDir);
            $zip->close();
            $filesystem->rename($tmpUnzipFullDir, $unzipDir);
            $filesystem->remove($tmpUnzipDir);
        } else {
            throw new \Exception($this->getKernel()->trans('无法解压缩安装包！'));
        }
    }

    protected function download($url, $tmp, $code)
    {
        // $filename = md5($url).'_'.time();
        $filepath = $tmp.DIRECTORY_SEPARATOR.$code.'.zip';

        $fp = fopen($filepath, 'w');

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_exec($curl);
        curl_close($curl);

        fclose($fp);

        return $filepath;
    }

    /**
     * @return CloudAppDaoImpl
     */
    protected function getAppDao()
    {
        return  $this->getServiceKernel()->createDao('CloudPlatform.CloudAppDao');
    }
}
