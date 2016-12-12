<?php

namespace Biz;

use Pimple\Container;
use Biz\Common\HTMLHelper;
use Pimple\ServiceProviderInterface;
use Biz\Testpaper\Builder\ExerciseBuilder;
use Biz\Testpaper\Builder\HomeworkBuilder;
use Biz\Testpaper\Builder\TestpaperBuilder;

class DefaultServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['html_helper'] = new HTMLHelper($biz);

        $biz['testpaper_builder.testpaper'] = function ($biz) {
            return new TestpaperBuilder($biz);
        };

        $biz['testpaper_builder.homework'] = function ($biz) {
            return new HomeworkBuilder($biz);
        };

        $biz['testpaper_builder.exercise'] = function ($biz) {
            return new ExerciseBuilder($biz);
        };
    }

}
