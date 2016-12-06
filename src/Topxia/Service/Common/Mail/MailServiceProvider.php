<?php

namespace Topxia\Service\Common\Mail;

use Pimple\ServiceProviderInterface;
use Topxia\Service\Common\Mail\MailFactory;


/**
* 
*/
class MailServiceProvider implements  ServiceProviderInterface
{
	// public function register($biz){
	// 	$biz['mail.factory'] = $biz->factory({
	// 		return new 
	// 	}
	// }

	public function register(Container $app)
    {
		$app['mail.factory'] = function ($app) {
			return new 
		};

    }
}