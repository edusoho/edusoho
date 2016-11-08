<?php

namespace Codeages\PluginBundle\System;

use Symfony\Component\HttpKernel\KernelInterface;

interface PluggableHttpKernelInterface extends KernelInterface
{
    /**
     * @return PluginConfigurationManagerInterface
     */
    public function getPluginConfigurationManager();
}
