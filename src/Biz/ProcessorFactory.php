<?php

namespace Biz;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;

class ProcessorFactory
{
    public static function create($targetType)
    {
        $targetType = explode($targetType, '.');
        if (empty($targetType)) {
            throw new InvalidArgumentException('类型不存在');
        }

        $class = __NAMESPACE__.'\\{$targetType[0]}'.ucfirst($targetType[1]).'Processor';

        return new $class(ServiceKernel::instance()->getBiz());
    }
}
