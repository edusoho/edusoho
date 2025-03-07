<?php

namespace Biz\StudyPlan\Strategy\Impl;

use Biz\StudyPlan\Strategy\TimeCalculationStrategy;

/**
 * 计算考试时长
 */
class TestpaperStrategy implements TimeCalculationStrategy
{
    public function calculateTime(array $activity): int
    {
        return $activity['ext']['limitedTime'];
    }
}
