<?php

namespace Biz\StudyPlan\Factory;

use Biz\StudyPlan\Strategy\Impl\FinishTimeTypeStrategy;
use Biz\StudyPlan\Strategy\Impl\MediaTypeStrategy;
use Biz\StudyPlan\Strategy\Impl\PptTypeStrategy;
use Biz\StudyPlan\Strategy\Impl\TestpaperStrategy;
use Biz\StudyPlan\Strategy\Impl\TextTypeStrategy;
use Biz\StudyPlan\Strategy\TimeCalculationStrategy;

class CalculationStrategyFactory
{
    public static function create(array $activity): TimeCalculationStrategy
    {
        // 优先处理 finishType 为 'time' 的情况
        if (isset($activity['finishType']) && 'time' === $activity['finishType']) {
            return new FinishTimeTypeStrategy();
        }

        $mediaType = $activity['mediaType'] ?? null;

        // 根据 mediaType 匹配策略
        switch ($mediaType) {
            case 'text':
                return new TextTypeStrategy();
            case 'video':
            case 'audio':
                return new MediaTypeStrategy();
            case 'ppt':
                return new PptTypeStrategy();
            case 'testpaper':
                return new TestpaperStrategy();
            default:
                throw new \InvalidArgumentException('Unsupported activity type: '.($mediaType ?? 'undefined'));
        }
    }
}
