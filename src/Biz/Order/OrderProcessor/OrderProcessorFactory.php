<?php

namespace Biz\Order\OrderProcessor;

use AppBundle\Common\JoinPointToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;

class OrderProcessorFactory
{
    /**
     * @param $type
     *
     * @throws InvalidArgumentException
     *
     * @return OrderProcessor
     */
    public static function create($type)
    {
        $map = JoinPointToolkit::load('order');

        if (!array_key_exists($type, $map)) {
            throw new InvalidArgumentException(sprintf('Unknown order type: %s', $type));
        }

        $class = $map[$type]['processor'];
        $biz = ServiceKernel::instance()->getBiz();

        return new $class($biz);
    }
}
