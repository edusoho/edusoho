<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\ActivityRuntimeContainerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use AppBundle\DependencyInjection\Compiler\ExtensionPass;
use AppBundle\Common\ExtensionalBundle;

class AppBundle extends ExtensionalBundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExtensionPass());
        $container->addCompilerPass(new ActivityRuntimeContainerPass());
    }

    public function getEnabledExtensions()
    {
        return array('DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate');
    }
}
