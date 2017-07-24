<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SessionService;

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
        return $this->getSessionDao()->deleteByUserId($userId);
    }

    public function deleteInvalidSession($sessionTime, $limit)
    {
        $sessions = $this->getSessionDao()->searchBySessionTime($sessionTime, $limit);
        $ids = ArrayToolKit::column($sessions, 'sess_id');

        return $this->getSessionDao()->deleteByIds($ids);
    }

    protected function getSessionDao()
    {
        return $this->createDao('System:SessionDao');
    }
}
