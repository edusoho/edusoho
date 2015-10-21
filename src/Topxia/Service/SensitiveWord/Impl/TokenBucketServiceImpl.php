<?php
namespace Topxia\Service\SensitiveWord\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\SensitiveWord\TokenBucketService;
use Topxia\Service\SensitiveWord\Type\QuestionTypeFactory;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\KeywordFilter;

class TokenBucketServiceImpl extends BaseService implements TokenBucketService
{
	public function getToken($ip)
	{
		if(in_array($ip, $this->getBlacklist())) {
			return false;
		}
		return true;
	}

	protected function getBlacklist()
	{

	}

}