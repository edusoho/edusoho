<?php

namespace Codeages\PluginBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CodeagesPluginBundle extends Bundle
{
    public function boot()
    {
        $biz = $this->container->get('biz');
        $biz['migration.directories'][] = __DIR__ . '/Migrations';
        $biz['autoload.aliases']['CodeagesPluginBundle'] = 'Codeages\PluginBundle\Biz';
    }
}
