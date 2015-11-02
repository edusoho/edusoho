<?php
namespace Topxia\Service\PostFilter\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\PostFilter\TokenBucketService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\KeywordFilter;

class TokenBucketServiceImpl extends BaseService implements TokenBucketService
{
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

	public function hasToken($ip, $type)
	{
		if(in_array($ip, $this->getIpBlacklist())) {
			return false;
		}

		$postNumRules = $this->getSettingService()->get("post_num_rules");

		if(!isset($postNumRules['rules'])) {
			return true;
		}

		foreach ($postNumRules['rules'] as $key => $value) {
			if($key == $type) {
				$rules = $postNumRules['rules'][$key];
				foreach ($rules as $ruleName => $rule) {
					if(!$this->confirmRule($ip, $ruleName, $rule)){
						return false;
					}
				}
			}
		}

		return true;
	}

	protected function confirmRule($ip, $type, $postNumRule)
	{
		$recentPostNum = $this->getRecentPostNumDao()->getRecentPostNumByIpAndType($ip, $type);
		if(empty($recentPostNum)) {
			$recentPostNum = $this->createRecentPostNum($ip, $type);
			return true;
		}

		if((time() - $recentPostNum['createdTime']) > $postNumRule["interval"]) {
			$this->getRecentPostNumDao()->deleteRecentPostNum($recentPostNum["id"]);
			$recentPostNum = $this->createRecentPostNum($ip, $type);
			return true;
		}

		if($recentPostNum['num'] < $postNumRule['postNum']) {
			$this->getRecentPostNumDao()->waveRecentPostNum($recentPostNum["id"], 'num', 1);
			return true;
		}

		return false;
	}

	protected function getIpBlacklist()
	{
		$postNumRules = $this->getSettingService()->get("post_num_rules");
		if(isset($postNumRules["ipBlackList"])) {
			return $postNumRules["ipBlackList"];
		}
		return array();
	}

	protected function getRecentPostNumDao()
    {
        return $this->createDao('PostFilter.RecentPostNumDao');
    }

    protected function getSettingService()
    {
        return $this->createDao('System.SettingService');
    }

}