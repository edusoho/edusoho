<?php
namespace Topxia\Service\Order\OrderRefundProcessor;

use Topxia\Service\Order\OrderRefundProcessor\OrderRefundProcessor;

class OrderRefundProcessorFactory
{

	public static function create($target)
    {
    	if(empty($target)) {
    		throw new Exception(self::getKernel()->trans('订单类型不存在'));
    	}

    	$class = __NAMESPACE__ . '\\' . ucfirst($target). 'OrderRefundProcessor';

    	return new $class();
    }
    protected function getKernel()
    {
        return ServiceKernel::instance();
    }


}


