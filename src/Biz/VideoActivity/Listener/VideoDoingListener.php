<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 2016/11/24
 * Time: 15:12
 */

namespace Biz\VideoActivity\Listener;


use Biz\Activity\Listener\Listener;

class VideoDoingListener extends Listener
{
    public function handle($activity, $data)
    {
        var_dump('VideoDoingListener', $activity, $data);
    }


}