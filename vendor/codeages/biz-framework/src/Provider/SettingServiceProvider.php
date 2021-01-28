<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SettingServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['autoload.aliases']['Setting'] = 'Codeages\Biz\Framework\Setting';

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Setting\Command\TableCommand($biz);
        };

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Setting\Command\SetCommand($biz);
        };
    }
}
