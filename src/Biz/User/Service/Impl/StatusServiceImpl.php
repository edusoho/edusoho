<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Dao\StatusDao;
use Biz\User\Service\StatusService;

class StatusServiceImpl extends BaseService implements StatusService
{
    public function publishStatus($status, $deleteOld = true)
    {
        if (!isset($status['userId'])) {
            $user = $this->getCurrentUser();

            if ($user['id'] == 0) {
                return array();
            }

            $status['userId'] = $user['id'];
        }

        $status['createdTime'] = time();
        $status['message'] = empty($status['message']) ? '' : $status['message'];
        if ($deleteOld) {
            $this->deleteOldStatus($status);
        }

        return $this->getStatusDao()->create($status);
    }

    protected function deleteOldStatus($status)
    {
        if (!empty($status['userId']) && !empty($status['type']) && !empty($status['objectType']) && !empty($status['objectId'])) {
            return $this->getStatusDao()->deleteByUserIdAndTypeAndObject($status['userId'], $status['type'], $status['objectType'], $status['objectId']);
        }

        return array();
    }

    public function searchStatuses($conditions, $sort, $start, $limit)
    {
        return $this->getStatusDao()->search($conditions, $sort, $start, $limit);
    }

    public function countStatuses($conditions)
    {
        return $this->getStatusDao()->count($conditions);
    }

    public function searchStatusesByUserIds($userIds, $start, $limit)
    {
        return $this->getStatusDao()->search(
            array(
                'userIds' => $userIds,
            ),
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );
    }

    public function deleteStatusesByCourseId($courseId)
    {
        return $this->getStatusDao()->deleteByCourseId($courseId);
    }

    /**
     * @return StatusDao
     */
    protected function getStatusDao()
    {
        return $this->createDao('User:StatusDao');
    }
}
