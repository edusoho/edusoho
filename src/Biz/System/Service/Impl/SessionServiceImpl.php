<?php

namespace Biz\System\Impl;

use Biz\System\SessionService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;

class SessionServiceImpl extends BaseService implements SessionService
{
    public function get($id)
    {
        return $this->getSessionDao()->get($id);
    }

    public function clear($id)
    {
        return $this->getSessionDao()->delete($id);
    }

    public function clearByUserId($userId)
    {
        return $this->getSessionDao()->deleteSessionByUserId($userId);
    }

    public function deleteInvalidSession($sessionTime, $limit)
    {
        $sessions = $this->getSessionDao()->findSessionsBySessionTime($sessionTime, $limit);
        $ids      = ArrayToolKit::column($sessions, "session_id");
        return $this->getSessionDao()->deleteSessionsByIds($ids);
    }

    protected function getSessionDao()
    {
        return $this->createDao('System.SessionDao');
    }

}
