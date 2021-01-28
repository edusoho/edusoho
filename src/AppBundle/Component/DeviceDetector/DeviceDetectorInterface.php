<?php

namespace AppBundle\Component\DeviceDetector;

interface DeviceDetectorInterface
{
    public function isMobile();

    public function getOs();

    public function isBot();

    public function getDevice();

    public function getClient();
}
