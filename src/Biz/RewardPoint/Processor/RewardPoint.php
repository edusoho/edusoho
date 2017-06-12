<?php

namespace Biz\RewardPoint\Processor;

use Codeages\Biz\Framework\Context\Biz;

abstract class RewardPoint
{
    protected $biz;

    abstract public function circulatingRewardPoint($params);

    abstract public function verifySettingEnable($params = null);

    abstract public function canCirculating($params);

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function waveRewardPoint($userId, $amount)
    {
        $account = $this->getAccountService()->getAccountByUserId($userId);
        if (empty($account)) {
            $account = $this->getAccountService()->createAccount(array('userId' => $userId));
        }

        return $this->getAccountService()->waveBalance($account['id'], $amount);
    }

    public function keepFlow($flow)
    {
        return $this->getAccountFlowService()->createAccountFlow($flow);
    }

    public function waveDownRewardPoint($userId, $amount)
    {
        $account = $this->getAccountService()->getAccountByUserId($userId);
        if (empty($account)) {
            $account = $this->getAccountService()->createAccount(array('userId' => $userId));
        }

        return $this->getAccountService()->waveDownBalance($account['id'], $amount);
    }

    protected function getUser()
    {
        return $this->biz['user'];
    }

    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }

    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}
