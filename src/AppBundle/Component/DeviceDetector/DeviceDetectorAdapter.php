<?php

namespace AppBundle\Component\DeviceDetector;

use DeviceDetector\DeviceDetector;
use Topxia\Service\Common\ServiceKernel;
use Doctrine\Common\Cache\PhpFileCache;

class DeviceDetectorAdapter implements DeviceDetectorInterface
{
    private $deviceDetector;

    public function __construct($userAgent)
    {
        $this->deviceDetector = new DeviceDetector($userAgent);
        $cachePath = $this->getCachePath();
        $this->deviceDetector->setCache(new PhpFileCache($cachePath));
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

    protected function getCachePath()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir') . '/cache/device_detector';
    }

}
