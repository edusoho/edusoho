<?php

namespace Biz\Activity\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\MaterialService;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Dao\DownloadFileRecordDao;
use Biz\Activity\Service\DownloadActivityService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

class DownloadActivityServiceImpl extends BaseService implements DownloadActivityService
{
    protected function createDownloadFileRecord($activity, $material)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw new AccessDeniedException();
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
            throw $this->createNotFoundException('activity not found');
        }

        if ($courseId != $activity['fromCourseId']) {
            throw $this->createInvalidArgumentException('activity not found in course');
        }

        $material = $this->getMaterialService()->getMaterial($activity['fromCourseId'], $materialId);

        if (empty($material)) {
            throw $this->createNotFoundException('file not found');
        }

        $downloadAvtivity = $activity['ext'];

        if (!isset($downloadAvtivity['fileIds'])) {
            throw $this->createInvalidArgumentException('not download activity');
        }

        if (!in_array($material['fileId'], $downloadAvtivity['fileIds'])) {
            throw $this->createNotFoundException('not activity file');
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
