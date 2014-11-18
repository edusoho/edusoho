<?php

namespace Topxia\Service\Util;

class CCVideoClientFactory
{
	public function createClient()
	{
		$class = __NAMESPACE__ . '\\CCVideoClient';
		$client = new $class();
		return $client;
	}
}