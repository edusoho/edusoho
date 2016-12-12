<?php

namespace Biz\Activity\Config;

use Biz\DocActivity\DocActivity;
use Biz\PptActivity\PptActivity;
use Biz\LiveActivity\LiveActivity;
use Biz\FlashActivity\FlashActivity;
use Codeages\Biz\Framework\Context\Biz;
use Biz\Activity\Type\Text\TextActivity;
use Biz\DiscussActivity\DiscussActivity;
use Biz\Activity\Type\Audio\AudioActivity;
use Biz\Activity\Type\Video\VideoActivity;
use Biz\DownloadActivity\DownloadActivity;
use Biz\ExerciseActivity\ExerciseActivity;
use Biz\HomeworkActivity\HomeworkActivity;
use Biz\TestpaperActivity\TestpaperActivity;

class ActivityFactory
{
    /**
     * @param  Biz        $biz
     * @param  $type
     * @return Activity
     */
    final public static function create(Biz $biz, $type)
    {
        $activities = self::all($biz);
        return $activities[$type];
    }

    final public static function all(Biz $biz)
    {
        return array(
            'text'      => new TextActivity($biz),
            'video'     => new VideoActivity($biz),
            'audio'     => new AudioActivity($biz),
            'live'      => new LiveActivity($biz),
            'discuss'   => new DiscussActivity($biz),
            'download'  => new DownloadActivity($biz),
            'flash'     => new FlashActivity($biz),
            'ppt'       => new PptActivity($biz),
            'doc'       => new DocActivity($biz),
            'testpaper' => new TestpaperActivity($biz),
            'exercise'  => new ExerciseActivity($biz),
            'homework'  => new HomeworkActivity($biz)
        );
    }
}
