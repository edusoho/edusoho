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
        // $this->fixPluginFile();
        $this->resetDeveloperSetting();
    }

    protected function updateWxPayConfig()
    {
        $payment = $this->getSettingService()->get('payment', array());
        $wxpay_enabled = empty($payment['wxpay_enabled']) ? 0 : $payment['wxpay_enabled'] ;
        if($wxpay_enabled){
            $loginConnect = $this->getSettingService()->get('login_bind', array());
            $weixinmob_key = empty($loginConnect['weixinmob_key']) ? '' : $loginConnect['weixinmob_key'];
            $weixinmob_secret = empty($loginConnect['weixinmob_secret']) ? '' : $loginConnect['weixinmob_secret'];
            
            $wxpay_appid = $payment['wxpay_appid'];
            $wxpay_secret = empty($payment['wxpay_secret']) ? '' : $payment['wxpay_secret'];
            if($wxpay_appid == $weixinmob_key && empty($wxpay_secret)){
                $payment['wxpay_secret'] = $weixinmob_secret; 
                $this->getSettingService()->set('payment', $payment);
            }
        }
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
        $pluginFile = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/plugin.php';
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($pluginFile)) {
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
