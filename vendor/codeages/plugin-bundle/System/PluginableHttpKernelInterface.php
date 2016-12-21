<?php

namespace Codeages\PluginBundle\System;

use Symfony\Component\HttpKernel\KernelInterface;

interface PluginableHttpKernelInterface extends KernelInterface
{
    public function getPluginConfigurationManager();
}
