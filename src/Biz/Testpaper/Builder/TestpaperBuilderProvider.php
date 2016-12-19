<?php

namespace Biz\Testpaper\Builder;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Context\Biz;
use Biz\Testpaper\Builder\ExerciseBuilder;
use Biz\Testpaper\Builder\HomeworkBuilder;
use Biz\Testpaper\Builder\TestpaperBuilder;

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
