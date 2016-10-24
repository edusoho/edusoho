<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 13:10
 */

namespace Biz\Activity\Config;


use Biz\TextActivity\TextActivity;
use Codeages\Biz\Framework\Context\Biz;

class ActivityFactory
{


    /**
     * @param Biz $biz
     * @param     $type
     * @return Activity
     */
    public final static function create(Biz $biz, $type)
    {
        $activities = self::all($biz);
        return $activities[$type];
    }

    public final static function all(Biz $biz)
    {
        return array(
            'text' => new TextActivity($biz)
        );
    }
}