<?php

namespace AgentBundle\Biz\StudyPlan\Strategy\Impl;

use AgentBundle\Biz\StudyPlan\Strategy\TimeCalculationStrategy;

/**
 * 时间型计算策略,针对设置了完成时间的课时
 */
class FinishTimeTypeStrategy implements TimeCalculationStrategy
{
    const SECONDS_PER_MINUTE = 60;

    public function calculateTime(array $activity): int
    {
        return $activity['finishData'] * self::SECONDS_PER_MINUTE;
    }
}
