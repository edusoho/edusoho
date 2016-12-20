<?php
namespace Biz\Order\OrderProcessor;

use Topxia\Common\JoinPointToolkit;
use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;

class OrderProcessorFactory
{
    public static function create($type)
    {
        $map = JoinPointToolkit::load('order');

        if (!array_key_exists($type, $map)) {
            throw new InvalidArgumentException(sprintf('Unknown order type: %s', $type));
        }

        $class = $map[$type]['processor'];
        return new $class();
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
