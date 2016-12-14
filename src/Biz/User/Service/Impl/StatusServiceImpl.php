<?php
namespace Biz\User\Impl;

use Biz\BaseService;
use Biz\User\Service\StatusService;

class StatusServiceImpl extends BaseService implements StatusService
{
    public function publishStatus($status, $deleteOld = true)
    {
        if (!isset($status["userId"])) {
            $user = $this->getCurrentUser();

            if ($user['id'] == 0) {
                return array();
            }

            $status['userId'] = $user['id'];
        }

        $status['createdTime'] = time();
        $status['message']     = empty($status['message']) ? '' : $status['message'];
        if ($deleteOld) {
            $this->deleteOldStatus($status);
        }

        return $this->getStatusDao()->addStatus($status);
    }

    protected function deleteOldStatus($status)
    {
        if (!empty($status['userId']) && !empty($status['type']) && !empty($status['objectType']) && !empty($status['objectId'])) {
            return $this->getStatusDao()->deleteStatusesByUserIdAndTypeAndObject($status['userId'], $status['type'], $status['objectType'], $status['objectId']);
        }
        return array();
    }

    public function searchStatuses($conditions, $sort, $start, $limit)
    {
        return $this->getStatusDao()->searchStatuses($conditions, $sort, $start, $limit);
    }

    public function searchStatusesCount($conditions)
    {
        return $this->getStatusDao()->searchStatusesCount($conditions);
    }

    public function findStatusesByUserIds($userIds, $start, $limit)
    {
        return $this->getStatusDao()->findStatusesByUserIds($userIds, $start, $limit);
    }

    protected function getStatusDao()
    {
        return $this->createDao('User.StatusDao');
    }
}
