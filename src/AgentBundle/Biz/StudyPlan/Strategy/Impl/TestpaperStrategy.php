<?php

namespace AgentBundle\Biz\StudyPlan\Strategy\Impl;

use AgentBundle\Biz\StudyPlan\Strategy\TimeCalculationStrategy;

/**
 * 计算考试时长
 */
class TestpaperStrategy implements TimeCalculationStrategy
{
    public function calculateTime(array $activity): int
    {
        $testPaperTime = 0;
        if ($activity['ext']['limitedTime'] != 0) {
            $testPaperTime = (int)$activity['ext']['limitedTime'];
        }
        if ($activity['length'] != 0) {
            $testPaperTime = $activity['length'];
        }
        return $testPaperTime;
    }
}
