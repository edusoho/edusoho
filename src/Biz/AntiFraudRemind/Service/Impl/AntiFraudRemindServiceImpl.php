<?php

namespace Biz\AntiFraudRemind\Service\Impl;

use Biz\AntiFraudRemind\Dao\AntiFraudRemindDao;
use Biz\AntiFraudRemind\Service\AntiFraudRemindService;
use Biz\BaseService;

class AntiFraudRemindServiceImpl extends BaseService implements AntiFraudRemindService
{
    public function getByUserId($userId)
    {
        return $this->getAntiFraudRemindDao()->getByUserId($userId);
    }

    public function creatAntiFraudRemind($antiFraudRemind)
    {
        return $this->getAntiFraudRemindDao()->create($antiFraudRemind);
    }

    public function updateLastRemindTime($fileId, $fields)
    {
        return $this->getAntiFraudRemindDao()->update($fileId, $fields);
    }

    /**
     * @return AntiFraudRemindDao
     */
    protected function getAntiFraudRemindDao()
    {
        return $this->createDao('AntiFraudRemind:AntiFraudRemindDao');
    }
}
