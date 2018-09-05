<?php

require_once 'dao/doc_activity_dao.php';

use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use doc\dao\doc_activity_dao;

class activity_doc extends Biz\Activity\Config\Activity
{
    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    public function create($fields)
    {
        if (empty($fields['media'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $fields['mediaId'] = $media['id'];

        $default = array(
            'finishDetail' => 1,
        );
        $fields = array_merge($default, $fields);

        $doc = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

        $biz = $this->getBiz();
        $doc['createdUserId'] = $biz['user']['id'];
        $doc['createdTime'] = time();

        $doc = $this->getDocActivityDao()->create($doc);

        return $doc;
    }

    public function copy($activity, $config = array())
    {
        $biz = $this->getBiz();
        $doc = $this->getDocActivityDao()->get($activity['mediaId']);
        $newDoc = array(
            'mediaId' => $doc['mediaId'],
            'finishType' => $doc['finishType'],
            'finishDetail' => $doc['finishDetail'],
            'createdUserId' => $biz['user']['id'],
        );

        return $this->getDocActivityDao()->create($newDoc);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceDoc = $this->getDocActivityDao()->get($sourceActivity['mediaId']);
        $doc = $this->getDocActivityDao()->get($activity['mediaId']);
        $doc['mediaId'] = $sourceDoc['mediaId'];
        $doc['finishType'] = $sourceDoc['finishType'];
        $doc['finishDetail'] = $sourceDoc['finishDetail'];

        return $this->getDocActivityDao()->update($doc['id'], $doc);
    }

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $doc = $this->getDocActivityDao()->get($activity['mediaId']);

        $result = $this->getTaskResultService()->getMyLearnedTimeByActivityId($activityId);
        $result /= 60;

        return !empty($result) && $result >= $doc['finishDetail'];
    }

    public function update($targetId, &$fields, $activity)
    {
        if (empty($fields['media'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $fields['mediaId'] = $media['id'];
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

        $updateFields['updatedTime'] = time();

        return $this->getDocActivityDao()->update($targetId, $updateFields);
    }

    public function delete($targetId)
    {
        return $this->getDocActivityDao()->delete($targetId);
    }

    public function get($targetId)
    {
        $activity = $this->getDocActivityDao()->get($targetId);
        $activity['file'] = $this->getUploadFileService()->getFullFile($activity['mediaId']);

        return $activity;
    }

    public function find($targetIds, $showCloud = 1)
    {
        $docActivities = $this->getDocActivityDao()->findByIds($targetIds);
        $mediaIds = ArrayToolkit::column($docActivities, 'mediaId');
        try {
            $files = $this->getUploadFileService()->findFilesByIds(
                $mediaIds,
                $showCloud = 1
            );
        } catch (CloudAPIIOException $e) {
            $files = array();
        }

        if (empty($files)) {
            return $docActivities;
        }
        $files = ArrayToolkit::index($files, 'id');
        array_walk(
            $docActivities,
            function (&$videoActivity) use ($files) {
                $videoActivity['file'] = isset($files[$videoActivity['mediaId']]) ? $files[$videoActivity['mediaId']] : null;
            }
        );

        return $docActivities;
    }

    public function materialSupported()
    {
        return true;
    }

    /**
     * @return DocActivityDao
     */
    protected function getDocActivityDao()
    {
        return $this->createDao(new doc_activity_dao($this->getBiz()));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
