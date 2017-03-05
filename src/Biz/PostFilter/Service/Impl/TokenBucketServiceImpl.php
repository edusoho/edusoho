<?php

namespace Biz\PostFilter\Service\Impl;

use Biz\BaseService;
use Biz\PostFilter\Dao\RecentPostNumDao;
use Biz\PostFilter\Service\TokenBucketService;
use Biz\System\Service\SettingService;

class TokenBucketServiceImpl extends BaseService implements TokenBucketService
{
    protected function createRecentPostNum($ip, $type)
    {
        $fields = array(
            'ip' => $ip,
            'type' => $type,
            'num' => 0,
            'createdTime' => time(),
        );

        return $this->getRecentPostNumDao()->create($fields);
    }

    public function incrToken($ip, $type)
    {
        $postNumRules = $this->getSettingService()->get('post_num_rules');

        if (!isset($postNumRules['rules'])) {
            return;
        }

        foreach ($postNumRules['rules'] as $key => $value) {
            if ($key == $type) {
                $rules = $postNumRules['rules'][$key];

                foreach ($rules as $ruleName => $rule) {
                    if (empty($rule['postNum'])) {
                        continue;
                    }

                    $ruleName = "{$key}.{$ruleName}";
                    $recentPostNum = $this->getRecentPostNumDao()->getByIpAndType($ip, $ruleName);

                    if (empty($recentPostNum)) {
                        $recentPostNum = $this->createRecentPostNum($ip, $ruleName);
                    }
                    $this->getRecentPostNumDao()->wave(array($recentPostNum['id']), array('num' => 1));
                }
            }
        }
    }

    public function hasToken($ip, $type)
    {
        if (in_array($ip, $this->getIpBlacklist())) {
            return false;
        }

        $postNumRules = $this->getSettingService()->get('post_num_rules');

        if (!isset($postNumRules['rules'])) {
            return true;
        }

        foreach ($postNumRules['rules'] as $key => $value) {
            if ($key == $type) {
                $rules = $postNumRules['rules'][$key];

                foreach ($rules as $ruleName => $rule) {
                    if (empty($rule['postNum'])) {
                        continue;
                    }

                    if (!$this->confirmRule($ip, "{$key}.{$ruleName}", $rule)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function confirmRule($ip, $type, $postNumRule)
    {
        $recentPostNum = $this->getRecentPostNumDao()->getByIpAndType($ip, $type);

        if (empty($recentPostNum)) {
            return true;
        }

        if ((time() - $recentPostNum['createdTime']) > $postNumRule['interval']) {
            $this->getRecentPostNumDao()->delete($recentPostNum['id']);

            return true;
        }

        if ($recentPostNum['num'] < $postNumRule['postNum']) {
            return true;
        }

        return false;
    }

    protected function getIpBlacklist()
    {
        $postNumRules = $this->getSettingService()->get('post_num_rules');

        if (isset($postNumRules['ipBlackList'])) {
            return $postNumRules['ipBlackList'];
        }

        return array();
    }

    /**
     * @return RecentPostNumDao
     */
    protected function getRecentPostNumDao()
    {
        return $this->createDao('PostFilter:RecentPostNumDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
