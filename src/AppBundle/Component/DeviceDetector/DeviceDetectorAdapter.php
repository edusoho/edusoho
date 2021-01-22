<?php

namespace AppBundle\Component\DeviceDetector;

use DeviceDetector\DeviceDetector;
use Doctrine\Common\Cache\PhpFileCache;
use Topxia\Service\Common\ServiceKernel;

class DeviceDetectorAdapter implements DeviceDetectorInterface
{
    private $deviceDetector;

    public function __construct($userAgent)
    {
        $this->deviceDetector = new DeviceDetector($userAgent);
        $cacheDir = $this->getCacheDir();
        $this->deviceDetector->setCache(new PhpFileCache($cacheDir));
        $this->deviceDetector->parse();
    }

    /**
     * @return bool
     */
    public function isMobile()
    {
        return $this->deviceDetector->isMobile();
    }

    public function getDevice()
    {
        return $this->deviceDetector->getDeviceName();
    }

    /**
     * @return array|string
     */
    public function getOs()
    {
        return $this->deviceDetector->getOs();
    }

    /**
     * @return bool
     */
    public function isBot()
    {
        return $this->deviceDetector->isBot();
    }

    public function getClient()
    {
        return $this->deviceDetector->getClient();
    }

    protected function getCacheDir()
    {
        $biz = $this->getServiceKernel()->getBiz();
        $activeTheme = $biz['pluginConfigurationManager']->getActiveThemeName() ?: 'Jianmo';

        return $this->getServiceKernel()->getParameter('kernel.root_dir').'/cache/'.$this->getServiceKernel()->getEnvironment().'/'.$activeTheme.'/device_detector';
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
