<?php

namespace AgentBundle\Biz\StudyPlan\Strategy\Impl;

use AgentBundle\Biz\StudyPlan\Strategy\TimeCalculationStrategy;

/**
 * 计算文本长度策略，主要针对文本学习内容
 */
class TextTypeStrategy implements TimeCalculationStrategy
{
    private const WORDS_PER_SECOND = 300;
    private const SECONDS_PER_MINUTE = 60;

    public function calculateTime(array $activity): int
    {
        return ceil(count($activity['content']) / self::WORDS_PER_SECOND * self::SECONDS_PER_MINUTE);
    }
}
