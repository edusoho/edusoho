<?php

namespace Biz\StudyPlan\Strategy\Impl;

use Biz\StudyPlan\Strategy\TimeCalculationStrategy;

/**
 * 计算音视频长度
 */
class MediaTypeStrategy implements TimeCalculationStrategy
{
    public function calculateTime(array $activity): int
    {
        return $activity['content']['length'];
    }
}
