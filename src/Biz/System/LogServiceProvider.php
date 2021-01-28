<?php

namespace Biz\System;

use Biz\System\Annotation\LogReader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class LogServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['service_log.annotation_reader'] = function ($biz) {
            if ($biz['debug']) {
                $cacheDirectory = null;
            } else {
                $cacheDirectory = $biz['cache_directory'].DIRECTORY_SEPARATOR.'service_log_interceptor_data';
            }

            return new LogReader($cacheDirectory);
        };

        $biz['interceptors']['log'] = '\Biz\System\Interceptor\AnnotationLogInterceptor';
    }
}
