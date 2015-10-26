<?php

namespace Topxia\Service\Card\CardDetailProcessor;

use Topxia\Service\Card\CardDetailProcessor\CardDetailProcessor;
use Exception;

class CardDetailFactory
{
	public static function create($cardType)
	{
		if(empty($cardType) || !in_array($cardType,array('coupon','moneyCard'))) {
    		throw new Exception("卡的类型不存在");
    	}
    	if($cardType == 'moneyCard') {
    		$cardType = 'money';
    	}

    	$class = __NAMESPACE__ . '\\' . ucfirst($cardType). 'CardDetailProcessor';

    	return new $class();
    	
	}
}