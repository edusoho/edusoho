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

    public function isMobile()
    {
        return $this->deviceDetector->isMobile();
    }

    /**
     * @return array|string
     */
    public function getOs()
    {
        return $this->deviceDetector->getOs();
    }
}
