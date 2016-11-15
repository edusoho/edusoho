<?php

namespace Biz\Activity\Config;

use Biz\DocActivity\DocActivity;
use Biz\PptActivity\PptActivity;
use Biz\LiveActivity\LiveActivity;
use Biz\TextActivity\TextActivity;
use Biz\AudioActivity\AudioActivity;
use Biz\FlashActivity\FlashActivity;
use Biz\VideoActivity\VideoActivity;
use Codeages\Biz\Framework\Context\Biz;
use Biz\DiscussActivity\DiscussActivity;
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
