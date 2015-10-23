<?php

namespace Topxia\Service\Card\CardDetailProcessor;

use Topxia\Service\Card\CardDetailProcessor\CardDetailProcessor;

class CardDetailFactory
{
	public static function create($cardType)
	{
		if(empty($target) || !in_array($target,array('coupon','moneyCard'))) {
    		throw new Exception("卡的类型不存在");
    	}
    	if($cardType == 'moneyCard') {
    		$cardType = 'money'
    	}

    	$class = __NAMESPACE__ . '\\' . ucfirst($target). 'CardDetailProcessor';

    	return new $class();
    	
	}
}