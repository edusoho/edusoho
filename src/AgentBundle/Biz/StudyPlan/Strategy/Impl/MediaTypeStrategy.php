<?php

namespace AgentBundle\Biz\StudyPlan\Strategy\Impl;

use AgentBundle\Biz\StudyPlan\Strategy\TimeCalculationStrategy;

/**
 * 计算音视频长度
 */
class MediaTypeStrategy implements TimeCalculationStrategy
{
    public function calculateTime(array $activity): int
    {
        return (int)$activity['length'];
    }
}
