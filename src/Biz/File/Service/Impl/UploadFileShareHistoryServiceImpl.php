<?php

namespace Biz\File\Service\Impl;

use Biz\BaseService;
use Biz\File\Dao\UploadFileShareHistoryDao;
use Biz\File\Service\UploadFileShareHistoryService;

class UploadFileShareHistoryServiceImpl extends BaseService implements UploadFileShareHistoryService
{
    public function getShareHistory($id)
    {
        return $this->getUploadFileShareHistoryDao()->get($id);
    }

    public function addShareHistory($sourceUserId, $targetUserId, $isActive)
    {
        $fileShareHistoryFields = array(
            'sourceUserId' => $sourceUserId,
            'targetUserId' => $targetUserId,
            'isActive' => $isActive,
            'createdTime' => time(),
        );

        return $this->getUploadFileShareHistoryDao()->create($fileShareHistoryFields);
    }

    public function findShareHistory($sourceUserId)
    {
        $shareHistories = $this->getUploadFileShareHistoryDao()->findByUserId($sourceUserId);

        return $shareHistories;
    }

    public function searchShareHistoryCount($conditions)
    {
        return $this->getUploadFileShareHistoryDao()->count($conditions);
    }

    public function searchShareHistories($conditions, $orderBy, $start, $limit)
    {
        return $this->getUploadFileShareHistoryDao()->search($conditions, $orderBy, $start, $limit);
    }

    /**
     * @return UploadFileShareHistoryDao
     */
    protected function getUploadFileShareHistoryDao()
    {
        return $this->createDao('File:UploadFileShareHistoryDao');
    }
}
