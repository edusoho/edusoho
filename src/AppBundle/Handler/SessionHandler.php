<?php

namespace AppBundle\Handler;

use Codeages\Biz\Framework\Session\Handler\BizSessionHandler;

class SessionHandler extends BizSessionHandler
{
    public function getLock($lockName, $lockTime = 30)
    {
        if (!$this->biz->offsetExists('session_redis')) {
            return parent::getLock($lockName, $lockTime);
        }

        $time = time();
        $sessionKey = "sess_{$lockName}";
        $exist = $this->getRedis()->get($sessionKey);
        if ($exist) {
            if ($exist > $time) {
                return false;
            }
            $this->getRedis()->del($sessionKey);
        }

        $result = $this->getRedis()->setnx($sessionKey, $time + $lockTime);
        if (!$result) {
            return false;
        }

        return $this->getRedis()->expire($sessionKey, $lockTime);
    }

    public function releaseLock($lockName)
    {
        if (!$this->biz->offsetExists('session_redis')) {
            return parent::releaseLock($lockName);
        }

        return $this->getRedis()->del('sess_{$lockName}');
    }

    private function getRedis()
    {
        return $this->biz['session_redis'];
    }
}
