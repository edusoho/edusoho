<?php

namespace Codeages\Biz\Framework\Session\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Session\Service\OnlineService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class OnlineServiceImpl extends BaseService implements OnlineService
{
    public function saveOnline($online)
    {
        if (!ArrayToolkit::requireds($online, array('sess_id'))) {
            throw new InvalidArgumentException('sess_id is required.');
        }
        $user = $this->biz['user'];
        if (!empty($user['id'])) {
            $online['user_id'] = $user['id'];
            $online['is_login'] = 1;
        } else {
            $online['user_id'] = 0;
            $online['is_login'] = 0;
        }

        $bizOptions = $this->biz['session.options'];
        $maxLifeTime = $bizOptions['max_life_time'];
        $online['deadline'] = time() + $maxLifeTime;

        $online = ArrayToolkit::parts($online, array(
            'sess_id',
            'deadline',
            'user_id',
            'is_login',
            'ip',
            'user_agent',
            'source',
        ));

        if (!empty($online['sess_id'])) {
            $savedOnine = $this->getOnlineBySessId($online['sess_id']);
            if (empty($savedOnine)) {
                return $this->getOnlineDao()->create($online);
            } else {
                return $this->getOnlineDao()->update($savedOnine['id'], $online);
            }
        }
    }

    public function getOnlineBySessId($sessId)
    {
        return $this->getOnlineDao()->getBySessId($sessId);
    }

    public function countLogined($gtAccessTime)
    {
        $condition = array(
            'active_time_GT' => $gtAccessTime,
            'is_login' => 1,
        );

        return $this->getOnlineDao()->count($condition);
    }

    public function countOnline($gtAccessTime)
    {
        $condition = array(
            'active_time_GT' => $gtAccessTime,
        );

        return $this->getOnlineDao()->count($condition);
    }

    public function gc()
    {
        return $this->getOnlineDao()->deleteByDeadlineLessThan(time());
    }

    public function searchOnlines($condition, $orderBy, $start, $limit)
    {
        return $this->getOnlineDao()->search($condition, $orderBy, $start, $limit);
    }

    public function countOnlines($condition)
    {
        return $this->getOnlineDao()->count($condition);
    }

    protected function getOnlineDao()
    {
        return $this->biz->dao('Session:OnlineDao');
    }
}
