<?php

namespace Biz;

use Pimple\Container;
use Biz\Common\HTMLHelper;
use Pimple\ServiceProviderInterface;
use Biz\File\FireWall\FireWallFactory;
use Biz\Testpaper\Builder\ExerciseBuilder;
use Biz\Testpaper\Builder\HomeworkBuilder;
use Biz\Testpaper\Builder\TestpaperBuilder;
use Biz\Sms\SmsProcessor\LessonSmsProcessor;
use Biz\Sms\SmsProcessor\LiveOpenLessonSmsProcessor;
use Biz\Announcement\Processor\AnnouncementProcessorFactory;

class DefaultServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['html_helper'] = function ($biz) {
            return new HTMLHelper($biz);
        };

        $biz['testpaper_builder.testpaper'] = function ($biz) {
            return new TestpaperBuilder($biz);
        };

        $biz['file_fire_wall_factory'] = function ($biz) {
            return new FireWallFactory($biz);
        };

        $biz['testpaper_builder.homework'] = function ($biz) {
            return new HomeworkBuilder($biz);
        };

        $biz['testpaper_builder.exercise'] = function ($biz) {
            return new ExerciseBuilder($biz);
        };

        $biz['announcement_processor'] = function ($biz) {
            return new AnnouncementProcessorFactory($biz);
        };

        $biz['sms_processor.lesson'] = function ($biz) {
            return new LessonSmsProcessor($biz);
        };

        $biz['sms_processor.liveOpen'] = function ($biz) {
            return new LiveOpenLessonSmsProcessor($biz);
        };

    }

}
