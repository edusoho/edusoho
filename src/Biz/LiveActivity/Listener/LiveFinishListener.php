<?php

namespace Biz\LiveActivity\Listener;

use Biz\Activity\Listener\Listener;

class LiveFinishListener extends Listener
{
    public function handle($activity, $data)
    {
        //TODO 如何判断任务已完成： 用户在指定时段观看了直播（不考虑观看时长？）
    }

}
