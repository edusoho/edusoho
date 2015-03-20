<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Filesystem\Filesystem;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Util\PluginUtil;


class PluginRegisterCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'plugin:register')
            ->addArgument('code', InputArgument::REQUIRED, '插件编码')
            ->setDescription('注册插件到EduSoho');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $code = $input->getArgument('code');
        $output->writeln("<comment>注册插件`{$code}`：</comment>");

        $pluginDir = dirname($this->getContainer()->getParameter('kernel.root_dir')) . '/plugins/' . $code;
        if (!is_dir($pluginDir)) {
            throw new \RuntimeException("插件目录{$pluginDir}不存在！");
        }
        $output->writeln("<comment>  - 检查插件目录...</comment><info>OK</info>");

        $meta = $this->parseMeta($code, $pluginDir);
        $output->writeln("<comment>  - 获取插件元信息...</comment><info>OK</info>");

        $this->executeInstall($pluginDir);
        $output->writeln("<comment>  - 执行安装脚本...</comment><info>OK</info>");

        $app = $this->getAppService()->registerApp($meta);
        $output->writeln("<comment>  - 添加应用记录...</comment><info>OK</info>");

        PluginUtil::refresh();
        $output->writeln("<comment>  - 刷新插件缓存...</comment><info>OK</info>");



        $output->writeln("<info>注册成功....</info>");

    }

    private function executeInstall($pluginDir)
    {
        $installFile = $pluginDir . '/Scripts/InstallScript.php';
        if (!file_exists($installFile)) {
            throw new \RuntimeException("插件安装脚本{$installFile}不存在！");
        }

        include $installFile;
        if (!class_exists('InstallScript')) {
            throw new \RuntimeException("插件脚本{$installFile}中，不存在InstallScript类。");
        }

        $installer = new \InstallScript(ServiceKernel::instance());
        $installer->setInstallMode('command');
        $installer->execute();
    }

    private function parseMeta($code, $pluginDir)
    {
        $metaFile = $pluginDir . '/plugin.json';
        if (!file_exists($metaFile)) {
            throw new \RuntimeException("插件元信息文件{$metaFile}不存在！");
        }

        $meta = json_decode(file_get_contents($metaFile), true);
        if (empty($meta)) {
            throw new \RuntimeException("插件元信息文件{$metaFile}格式不符合JSON规范，解析失败，请检查元信息文件格式");
        }

        if (empty($meta['code']) or empty($meta['name']) or empty($meta['version'])) {
            throw new \RuntimeException("插件元信息必须包含code、name、version属性");
        }

        if ($meta['code'] != $code) {
            throw new \RuntimeException("插件元信息code的值`{$meta['code']}`不正确，应为`{$code}`。");
        }

        return $meta;
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    private function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());
        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 1,
            'nickname' => '游客',
            'currentIp' =>  '127.0.0.1',
            'roles' => array(),
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }

}