<?php
namespace Topxia\Service\PostFilter\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\PostFilter\TokenBucketService;
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

	protected function createRecentPostNum($ip, $type)
	{
		$fields = array(
			'ip' => $ip,
			'type' => $type,
			'num' => 1,
			'createdTime' => time(), 
		);
		return $this->getRecentPostNumDao()->addRecentPostNum($fields);
	}

	protected function hasToken($ip, $type)
	{
		$recentPostNum = $this->getRecentPostNumDao()->getRecentPostNumByIpAndType($ip, $type);
		if(empty($recentPostNum)) {
			$recentPostNum = $this->createRecentPostNum($ip, $type);
			return true;
		} 

		$postNumSetting = $this->getSettingService()->get("post_num_rules");
		if(!isset($postNumSetting[$type])) {
			return true;
		}

		$postNumSetting = $postNumSetting[$type];
		if((time() - $recentPostNum['createdTime']) > $postNumSetting["interval"]) {
			$this->getRecentPostNumDao()->deleteRecentPostNum($recentPostNum["id"]);
			$recentPostNum = $this->createRecentPostNum($ip, $type);
			return true;
		}

		if($recentPostNum['num'] < $postNumSetting['postNum']) {
			$this->getRecentPostNumDao()->waveRecentPostNum($recentPostNum["id"], 'num', 1);
			return true;
		}

		return false;
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