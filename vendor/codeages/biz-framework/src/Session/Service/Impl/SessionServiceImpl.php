<?php

namespace Codeages\Biz\Framework\Session\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Session\Service\SessionService;

class SessionServiceImpl extends BaseService implements SessionService
{
    public function createSession($session)
    {
        return $this->getSessionDao()->create($session);
    }

    public function deleteSessionBySessId($sessId)
    {
        return $this->getSessionDao()->deleteBySessId($sessId);
    }

    public function updateSessionBySessId($sessId, $session)
    {
        $savedSession = $this->getSessionDao()->getBySessId($sessId);
        return $this->getSessionDao()->update($savedSession['id'], $session);
    }

    public function searchSessions($condition, $orderBy, $start, $limit)
    {
        return $this->getSessionDao()->search($condition, $orderBy, $start, $limit);
    }

    public function countLogined($gtSessTime)
    {
        return $this->getSessionDao()->countLogined($gtSessTime);
    }

    public function countSessions($condition)
    {
        return $this->getSessionDao()->count($condition);
    }

    public function countTotal($gtSessTime)
    {
        return $this->getSessionDao()->countTotal($gtSessTime);
    }

    public function gc()
    {
        return $this->getSessionDao()->deleteByInvalid();
    }

    public function getSessionBySessId($sessId)
    {
        return $this->getSessionDao()->getBySessId($sessId);
    }

    protected function getSessionDao()
    {
        return $this->biz->dao('Session:SessionDao');
    }
}