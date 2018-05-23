<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Targetlog\Command\TableCommand;

class TargetlogServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['autoload.aliases']['Targetlog'] = 'Codeages\Biz\Framework\Targetlog';

        $biz['console.commands'][] = function () use ($biz) {
            return new TableCommand($biz);
        };

        $biz['interceptors']['target_log'] = '\Codeages\Biz\Framework\Targetlog\Interceptor\AnnotationInterceptor';
    }
}
