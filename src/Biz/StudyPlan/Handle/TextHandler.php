<?php

namespace Biz\StudyPlan\Handle;

class TextHandler implements ActivityHandler
{
    // 按300字每分钟计算
    public function handle(array $activities): int
    {
        $totalTime = 0;
        foreach ($activities as $activity) {
            if ('time' == $activity['finishType']) {
                $totalTime += $activity['finishData'] * 60;
            } else {
                $totalTime += ceil($activity['content'] / 300 * 60);
            }
        }

        return $totalTime;
    }
}
