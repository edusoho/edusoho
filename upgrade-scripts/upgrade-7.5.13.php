<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Component\Payment\Payment;
use Topxia\Service\Util\PluginUtil;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $this->updateWxPayConfig();
        $this->fixPluginFile();
        $this->resetDeveloperSetting();
    }

    protected function updateWxPayConfig()
    {
        $payment = $this->getSettingService()->get('payment', array());
        $payment['wxpay_appid'] =   $payment['wxpay_key'];
        $payment['wxpay_key'] =     $payment['wxpay_secret'];
        $payment['wxpay_secret'] =  '';


        $this->getSettingService()->set('payment', $payment);
    }

    protected function resetDeveloperSetting()
    {
        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }
    private function fixPluginFile()
    {
        $pluginFile = ServiceKernel::instance()->getParameter('kernel.root.dir').'/config/plugin.php';
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($fileSystem)) {
            return;
        }
        $fileSystem->remove($pluginFile);
        PluginUtil::refresh();
    }

    protected function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}
