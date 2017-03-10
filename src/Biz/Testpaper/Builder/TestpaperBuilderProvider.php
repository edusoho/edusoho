<?php

namespace Biz\Testpaper\Builder;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TestpaperBuilderProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['testpaperBuilder.testpaper'] = function ($app) {
            return new TestpaperBuilder($app);
        };

        $app['testpaperBuilder.homework'] = function ($app) {
            return new HomeworkBuilder($app);
        };

        $app['testpaperBuilder.exercise'] = function ($app) {
            return new ExerciseBuilder($app);
        };
    }
}
