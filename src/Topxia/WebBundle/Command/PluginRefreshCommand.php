<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

class PluginRefreshCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'plugin:refresh' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $this->filesystem = new Filesystem();

        $count = $this->getAppService()->findAppCount();
        $apps = $this->getAppService()->findApps(0, $count);

        $this->refreshMetaFile($apps);
        $this->refreshRoutingFile($apps);


    }

    protected function refreshMetaFile($apps)
    {
        $pluginMetas = array(
            'protocol' => '1.0',
            'installed' => array()
        );

        foreach ($apps as $app) {
            if ($app['code'] == 'MAIN') {
                continue;
            }

            $pluginMetas['installed'][] = $app['code'];
        }

        $dataDirectory = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/data/');
        if (empty($dataDirectory)) {
            throw new \RuntimeException('app/data目录不存在，请先创建');
        }

        $metaFilePath = $dataDirectory . '/plugin_installed.php';
        if ($this->filesystem->exists($metaFilePath)) {
            $this->filesystem->remove($metaFilePath);
        }

        $fileContent = "<?php \nreturn " . var_export($pluginMetas, true) . ";";
        file_put_contents($metaFilePath, $fileContent);
    }

    protected function refreshRoutingFile($apps)
    {
        $pluginRootDirectory = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../plugins');

        $config = '';

        foreach ($apps as $app) {
            if ($app['code'] == 'MAIN') {
                continue;
            }
            $code = $app['code'];


            $routingPath = sprintf("{$pluginRootDirectory}/%s/%sBundle/Resources/config/routing.yml", ucfirst($code), ucfirst($code));
            if ($this->filesystem->exists($routingPath)) {
                $config .= "_plugin_{$code}_web:\n";
                $config .= sprintf("    resource: \"@%sBundle/Resources/config/routing.yml\"\n", ucfirst($code));
                $config .= "    prefix:   /\n";
            }

            $routingPath = sprintf("{$pluginRootDirectory}/%s/%sBundle/Resources/config/routing_admin.yml", ucfirst($code), ucfirst($code));
            if ($this->filesystem->exists($routingPath)) {
                $config .= "_plugin_{$code}_admin:\n";
                $config .= sprintf("    resource: \"@%sBundle/Resources/config/routing_admin.yml\"\n", ucfirst($code));
                $config .= "    prefix:   /admin\n";
            }
        }

        $pluginRouteFilePath = $this->getContainer()->getParameter('kernel.root_dir') . '/config/routing_plugins.yml';
        if (!$this->filesystem->exists($pluginRouteFilePath)) {
            $this->filesystem->touch($pluginRouteFilePath);
        }

        file_put_contents($pluginRouteFilePath, $config);

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