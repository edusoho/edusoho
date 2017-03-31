<?php

namespace Biz\Course\CourseProcessor;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;

class CourseProcessorFactory
{
    public static function create($targetType)
    {
        if (empty($targetType)) {
            throw new InvalidArgumentException('...类型不存在');
        }

        $class = __NAMESPACE__.'\\'.ucfirst($targetType).'Processor';

        return new $class(ServiceKernel::instance()->getBiz());
    }
}
