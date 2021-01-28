<?php

namespace AppBundle\Component\DeviceDetector;

use DeviceDetector\DeviceDetector;

class DeviceDetectorAdapter implements DeviceDetectorInterface
{
    private $deviceDetector;

    public function __construct($userAgent)
    {
        $this->deviceDetector = new DeviceDetector($userAgent);
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
}
