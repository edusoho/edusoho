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

            if (0 == $user['id']) {
                return [];
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
        if (empty($status['classroomId'])) {
            $conditions = ['courseId' => $status['courseId']];
            $recentStatuses = $this->searchStatuses($conditions, ['createdTime' => 'desc'], 0, 5);
        } else {
            $conditions = ['onlyClassroomId' => $status['classroomId']];
            $recentStatuses = $this->searchStatuses($conditions, ['createdTime' => 'desc'], 0, 10);
        }
        if ($recentStatuses) {
            $conditions['createdTime_LT'] = end($recentStatuses)['createdTime'];
            $this->getStatusDao()->batchDelete($conditions);
        }
        if (!empty($status['userId']) && !empty($status['type']) && !empty($status['objectType']) && !empty($status['objectId'])) {
            return $this->getStatusDao()->deleteByUserIdAndTypeAndObject($status['userId'], $status['type'], $status['objectType'], $status['objectId']);
        }

        return [];
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
            [
                'userIds' => $userIds,
            ],
            ['createdTime' => 'DESC'],
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
