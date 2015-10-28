<?php
namespace Topxia\Service\SensitiveWord\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\SensitiveWord\TokenBucketService;
use Topxia\Service\SensitiveWord\Type\QuestionTypeFactory;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\KeywordFilter;

class TokenBucketServiceImpl extends BaseService implements TokenBucketService
{
	public function getToken($ip, $type)
	{
		if(in_array($ip, $this->getBlacklist())) {
			return false;
		}

		return $this->hasToken($ip, $type);

	}

	protected function hasToken($ip, $type)
	{
		$recentPostNum = $this->getRecentPostNumDao()->getRecentPostNumByIpAndType($ip, $type);
		if(empty($recentPostNum)) {
			return true;
		} 

		$postNumSetting = $this->getSettingService()->get('post_num_setting');
		if((time() - $recentPostNum['createdTime']) > $postNumSetting[""]) {
			
		}
	}

	protected function getBlacklist()
	{
		return array();
	}

	protected function getRecentPostNumDao()
    {
        return $this->createDao('SensitiveWord.RecentPostNumDao');
    }

    protected function getSettingService()
    {
        return $this->createDao('System.SettingService');
    }

}