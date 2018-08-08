<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Targetlog\Annotation\LogReader;
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

        $biz['service_targetlog.annotation_reader'] = function ($biz) {
            if ($biz['debug']) {
                $cacheDirectory = null;
            } else {
                $cacheDirectory = $biz['cache_directory'].DIRECTORY_SEPARATOR.'service_targetlog_interceptor_data';
            }

            return new LogReader($cacheDirectory);
        };

        if (!empty($biz['targetlog.interceptor_enable'])) {
            $biz['interceptors']['target_log'] = '\Codeages\Biz\Framework\Targetlog\Interceptor\AnnotationInterceptor';
        }
    }
}
