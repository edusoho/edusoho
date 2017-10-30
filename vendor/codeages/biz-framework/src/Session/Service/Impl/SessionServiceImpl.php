<?php

namespace Codeages\Biz\Framework\Session\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Session\Service\SessionService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class SessionServiceImpl extends BaseService implements SessionService
{
    public function saveSession($session)
    {
        if (!ArrayToolkit::requireds($session, array('sess_id', 'sess_data'))) {
            throw new InvalidArgumentException('args is invalid.');
        }

        $session['sess_deadline'] = time() + $this->getMaxLifeTime();

        return $this->getSessionStorage()->save($session);
    }

    public function deleteSessionBySessId($sessId)
    {
        return $this->getSessionStorage()->delete($sessId);
    }

    public function getSessionBySessId($sessId)
    {
        return $this->getSessionStorage()->get($sessId);
    }

    public function gc()
    {
        return $this->getSessionStorage()->gc(time());
    }

    protected function getMaxLifeTime()
    {
        return $this->biz['session.options']['max_life_time'];
    }

    protected function getSessionStorage()
    {
        return $this->biz['session.storage.'.$this->biz['session.options']['session_storage']];
    }
}
