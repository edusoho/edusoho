<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SettingServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/setting';
        $container['autoload.aliases']['Setting'] = 'Codeages\Biz\Framework\Setting';
    }
}
