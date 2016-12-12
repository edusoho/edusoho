<?php

namespace Biz\Activity\Config;


use Biz\Activity\Type\Audio\AudioActivity;
use Biz\Activity\Type\Download\Download;
use Biz\Activity\Type\Text\TextActivity;
use Biz\Activity\Type\Video\VideoActivity;
use Biz\DiscussActivity\DiscussActivity;
use Biz\DocActivity\DocActivity;
use Biz\FlashActivity\FlashActivity;
use Biz\LiveActivity\LiveActivity;
use Biz\PptActivity\PptActivity;
use Codeages\Biz\Framework\Context\Biz;

class ActivityFactory
{
    /**
     * @param  Biz $biz
     * @param      $type
     *
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
            'text'     => new TextActivity($biz),
            'video'    => new VideoActivity($biz),
            'audio'    => new AudioActivity($biz),
            'live'     => new LiveActivity($biz),
            'discuss'  => new DiscussActivity($biz),
            'download' => new Download($biz),
            'flash'    => new FlashActivity($biz),
            'ppt'      => new PptActivity($biz),
            'doc'      => new DocActivity($biz)
        );
    }
}
