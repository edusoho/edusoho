<?php
namespace Topxia\Service\Sms\SmsProcessor;

use Topxia\Service\Sms\SmsProcessor\SmsProcessor;

class SmsProcessorFactory
{

	public static function create($targetType)
    {
    	if(empty($targetType)) {
    		throw new Exception(self::getKernel()->trans('短信类型不存在'));
    	}

    	$class = __NAMESPACE__ . '\\' . ucfirst($targetType). 'SmsProcessor';

    	return new $class();
    }
    
    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

}


