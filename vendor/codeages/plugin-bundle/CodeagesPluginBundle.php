<?php

namespace Codeages\PluginBundle;

use Codeages\PluginBundle\DependencyInjection\Compiler\EventSubscriberPass;
use Codeages\PluginBundle\Event\LazyDispatcher;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CodeagesPluginBundle extends Bundle
{
    public function boot()
    {
        $biz = $this->container->get('biz');
        $container = $this->container;
        $biz['dispatcher'] = function () use ($container) {
            return new LazyDispatcher($container);
        };

        $biz['subscribers'] = new \ArrayObject();
        $biz['migration.directories'][] = __DIR__.'/Migrations';
        $biz['autoload.aliases']['CodeagesPluginBundle'] = 'Codeages\PluginBundle\Biz';
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EventSubscriberPass());
    }
}
