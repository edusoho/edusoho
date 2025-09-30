<?php

namespace AgentBundle\Biz\StudyPlan\Factory;

use AgentBundle\Biz\StudyPlan\Strategy\Impl\DefaultStrategy;
use AgentBundle\Biz\StudyPlan\Strategy\Impl\FinishTimeTypeStrategy;
use AgentBundle\Biz\StudyPlan\Strategy\Impl\MediaTypeStrategy;
use AgentBundle\Biz\StudyPlan\Strategy\Impl\TestpaperStrategy;
use AgentBundle\Biz\StudyPlan\Strategy\TimeCalculationStrategy;

class CalculationStrategyFactory
{
    public static function create(array $activity): TimeCalculationStrategy
    {
        // 优先处理 finishType 为 'time' 的情况
        if (isset($activity['finishType']) && in_array($activity['finishType'], ['time', 'watchTime'])) {
            return new FinishTimeTypeStrategy();
        }

        $mediaType = $activity['mediaType'] ?? null;

        // 根据 mediaType 匹配策略
        switch ($mediaType) {
            case 'video':
            case 'audio':
                return new MediaTypeStrategy();
            case 'testpaper':
                return new TestpaperStrategy();
            default:
                return new DefaultStrategy();
        }
    }
}
