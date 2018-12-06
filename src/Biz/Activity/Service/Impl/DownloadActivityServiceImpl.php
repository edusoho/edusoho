<?php

namespace Biz\Activity\Service\Impl;

use Biz\Activity\ActivityException;
use Biz\Activity\DownloadActivityException;
use Biz\BaseService;
use Biz\Course\MaterialException;
use Biz\Course\Service\MaterialService;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Dao\DownloadFileRecordDao;
use Biz\Activity\Service\DownloadActivityService;
use Biz\User\UserException;

class DownloadActivityServiceImpl extends BaseService implements DownloadActivityService
{
    protected function createDownloadFileRecord($activity, $material)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }
        $record = array(
            'downloadActivityId' => $activity['id'],
            'materialId' => $material['id'],
            'fileId' => $material['fileId'],
            'link' => $material['link'],
            'userId' => $user->getId(),
        );

        return $this->getDownloadFileRecordDao()->create($record);
    }

    public function downloadActivityFile($courseId, $activityId, $materialId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, $fetchMedia = true);
        if (empty($activity)) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }

        if ($courseId != $activity['fromCourseId']) {
            $this->createNewException(ActivityException::ACTIVITY_NOT_IN_COURSE());
        }

        $material = $this->getMaterialService()->getMaterial($activity['fromCourseId'], $materialId);

        if (empty($material)) {
            $this->createNewException(MaterialException::NOTFOUND_MATERIAL());
        }

        $downloadAvtivity = $activity['ext'];

        if (!isset($downloadAvtivity['fileIds'])) {
            $this->createNewException(DownloadActivityException::NOT_DOWNLOAD_ACTIVITY());
        }

        if (!in_array($material['fileId'], $downloadAvtivity['fileIds']) && !in_array($material['link'], $downloadAvtivity['fileIds'])) {
            $this->createNewException(DownloadActivityException::FILE_NOT_IN_ACTIVITY());
        }
        $this->createDownloadFileRecord($activity, $material);

        return $material;
    }

    /**
     * @return DownloadFileRecordDao
     */
    protected function getDownloadFileRecordDao()
    {
        return $this->createDao('Activity:DownloadFileRecordDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->biz->service('Course:MaterialService');
    }
}
