<?php

namespace Codeages\PluginBundle\System;

use Symfony\Component\HttpKernel\KernelInterface;

interface PluginableHttpKernelInterface extends KernelInterface
{
    /**
     * @return PluginConfigurationManager
     */
    public function getPluginConfigurationManager();
}
