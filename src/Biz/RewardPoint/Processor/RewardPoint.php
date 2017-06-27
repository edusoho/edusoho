<?php

namespace Biz\RewardPoint\Processor;

use Codeages\Biz\Framework\Context\Biz;

abstract class RewardPoint
{
    protected $biz;

    abstract public function canReward($params);

    abstract public function generateFlow($params);

    public function reward($params)
    {
        if ($this->canReward($params)) {
            $flow = $this->keepFlow($this->generateFlow($params));
            $this->waveRewardPoint($flow['userId'], $flow['amount']);
            $user = $this->getUser();
            if ($params['way'] != 'elite_thread') {
                $user['Reward-Point-Notify'] = array('type' => 'inflow', 'amount' => $flow['amount'], 'way' => $params['way']);
            }
        }
    }

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

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}
