<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TargetlogServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/targetlog';
        $container['autoload.aliases']['Targetlog'] = 'Codeages\Biz\Framework\Targetlog';
    }
}
