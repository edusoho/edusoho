<?php

namespace Biz\DownloadActivity\Service\Impl;

use Biz\BaseService;
use Biz\DownloadActivity\Dao\DownloadFileRecordDao;
use Biz\DownloadActivity\Service\DownloadActivityService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Topxia\Common\ArrayToolkit;

class DownloadActivityServiceImpl extends BaseService implements DownloadActivityService
{
    public function createDownloadFileRecord($downloadFile)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw new AccessDeniedException();
        }
        $record = array(
            'downloadActivityId' => $downloadFile['downloadActivityId'],
            'downloadFileId'     => $downloadFile['id'],
            'fileIndicate'       => $downloadFile['indicate'],
            'userId'             => $user->getId()
        );

        return $this->getDownloadFileRecordDao()->create($record);
    }

    public function downloadActivityFile($activityId, $downloadFileId)
    {
        $activity = $this->getActivityService()->getActivityFetchMedia($activityId);

        $materials = empty($activity['ext']['materials']) ? array() : $activity['ext']['materials'];
        if (empty($materials)) {
            throw $this->createNotFoundException('activity not found');
        }
        $downloadFiles = ArrayToolkit::index($materials, 'id');
        $downloadFile  = $downloadFiles[$downloadFileId];
        if (empty($downloadFile)) {
            throw $this->createNotFoundException('file not found');
        }
        $this->createDownloadFileRecord($downloadFile);

        return $downloadFile;
    }


    /**
     * @return DownloadFileRecordDao
     */
    protected function getDownloadFileRecordDao()
    {
        return $this->createDao('DownloadActivity:DownloadFileRecordDao');
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

}
