<?php

namespace Biz\StudyPlan\Factory;

use Biz\StudyPlan\Handle\ActivityHandler;
use Biz\StudyPlan\Handle\DocumentReplayHandler;
use Biz\StudyPlan\Handle\LiveHandler;
use Biz\StudyPlan\Handle\MediaHandler;
use Biz\StudyPlan\Handle\pptHandler;
use Biz\StudyPlan\Handle\TestpaperHandler;
use Biz\StudyPlan\Handle\TextHandler;

class ActivityHandlerFactory
{
    private static $handlers = [
        'text' => TextHandler::class,
        'video' => MediaHandler::class,
        'audio' => MediaHandler::class,
        'live' => LiveHandler::class,
        'testpaper' => TestpaperHandler::class,
        'ppt' => pptHandler::class,
        'doc' => DocumentReplayHandler::class,  // 文档类型
        'replay' => DocumentReplayHandler::class,  // 回放类型'
    ];

    public static function createHandler(string $type): ActivityHandler
    {
        if (!isset(self::$handlers[$type])) {
            throw new \InvalidArgumentException("Unsupported type: $type");
        }
        $handlerClass = self::$handlers[$type];

        return new $handlerClass();
    }
}
