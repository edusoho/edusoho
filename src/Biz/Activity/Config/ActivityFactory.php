<?php

namespace Biz\Activity\Config;


use Biz\AudioActivity\AudioActivity;
use Biz\LiveActivity\LiveActivity;
use Biz\TextActivity\TextActivity;
use Biz\VideoActivity\VideoActivity;
use Codeages\Biz\Framework\Context\Biz;
use Biz\DiscussActivity\DiscussActivity;

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
            'text'  => new TextActivity($biz),
            'video' => new VideoActivity($biz),
            'audio' => new AudioActivity($biz),
            'live'  => new LiveActivity($biz),
            'discuss' => new DiscussActivity($biz)
        );
    }
}
