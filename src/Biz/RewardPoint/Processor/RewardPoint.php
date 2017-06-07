<?php

namespace Biz\RewardPoint\Processor;

use Codeages\Biz\Framework\Context\Biz;

abstract class RewardPoint
{
    protected $biz;

    abstract public function circulatingRewardPoint($param);

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

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}