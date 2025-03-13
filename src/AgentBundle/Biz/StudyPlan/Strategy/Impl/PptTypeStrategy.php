<?php

namespace AgentBundle\Biz\StudyPlan\Strategy\Impl;

use AgentBundle\Biz\StudyPlan\Strategy\TimeCalculationStrategy;

class PptTypeStrategy implements TimeCalculationStrategy
{
    private const SECOND_PER_PAGE = 120;

    public function calculateTime(array $activity): int
    {
        return $activity['content']['length'] * self::SECOND_PER_PAGE;
    }
}
