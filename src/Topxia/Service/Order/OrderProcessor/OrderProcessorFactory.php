<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Common\JoinPointToolkit;
use Topxia\Service\Order\OrderProcessor\OrderProcessor;

class OrderProcessorFactory
{

	public static function create($type)
    {
			$map = JoinPointToolkit::load('order');

			if (!array_key_exists($type, $map)) {
					throw new NotFoundException('订单类型不存在: ' . $type);
			}

			$class = $map[$type]['processor'];
			return new $class();
    }

}
