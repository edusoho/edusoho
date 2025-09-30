<?php

namespace AgentBundle\Biz\StudyPlan\Strategy;

interface TimeCalculationStrategy
{
    public function calculateTime(array $activity): int;
}
