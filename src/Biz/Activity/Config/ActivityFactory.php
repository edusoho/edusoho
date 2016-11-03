<?php

namespace Biz\Activity\Config;

use Biz\FlashActivity\FlashActivity;
use Biz\LiveActivity\LiveActivity;
use Biz\PptActivity\PptActivity;
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
            'video' => new VideoActivity($biz),
            'text'    => new TextActivity($biz),
            'live'  => new LiveActivity($biz),
            'discuss' => new DiscussActivity($biz),
            'ppt' => new PptActivity($biz),
            'flash' => new FlashActivity($biz)
        );
    }
}
