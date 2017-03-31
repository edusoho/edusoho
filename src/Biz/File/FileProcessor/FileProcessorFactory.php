<?php

namespace Biz\File\FileProcessor;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;

class FileProcessorFactory
{
    public static function create($targetType)
    {
        if (empty($targetType)) {
            throw new InvalidArgumentException('文件类型不存在');
        }

        $class = __NAMESPACE__.'\\'.ucfirst($targetType).'FileProcessor';

        return new $class(ServiceKernel::instance()->getBiz());
    }
}
