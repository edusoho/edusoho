<?php

namespace AppBundle\Component\DeviceDetector;

interface DeviceDetectorInterface
{
    public function isMobile();

    public function getOs();
}
