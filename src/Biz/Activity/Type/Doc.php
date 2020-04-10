<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\DocActivityDao;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Common\CommonException;
use Biz\File\Service\UploadFileService;
use Biz\Activity\Service\ActivityService;

class Doc extends Activity
{
    public function registerActions()
    {
        return array(
            'create' => 'AppBundle:Doc:create',
            'edit' => 'AppBundle:Doc:edit',
            'show' => 'AppBundle:Doc:show',
        );
    }

    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    public function create($fields)
    {
        if (empty($fields['media'])) {
            throw CommonException::ERROR_PARAMETER();
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw CommonException::ERROR_PARAMETER();
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

        $user = $this->getCurrentUser();
        $doc['createdUserId'] = $user['id'];
        $doc['createdTime'] = time();

        $doc = $this->getDocActivityDao()->create($doc);

        return $doc;
    }

    public function copy($activity, $config = array())
    {
        $user = $this->getCurrentUser();
        $doc = $this->getDocActivityDao()->get($activity['mediaId']);
        $newDoc = array(
            'mediaId' => $doc['mediaId'],
            'finishType' => $doc['finishType'],
            'finishDetail' => $doc['finishDetail'],
            'createdUserId' => $user['id'],
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

    public function update($targetId, &$fields, $activity)
    {
        if (empty($fields['media'])) {
            throw CommonException::ERROR_PARAMETER();
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw CommonException::ERROR_PARAMETER();
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

        if ($activity) {
            $activity['file'] = $this->getUploadFileService()->getFullFile($activity['mediaId']);
        }

        return $activity;
    }

    public function find($targetIds, $showCloud = 1)
    {
        $docActivities = $this->getDocActivityDao()->findByIds($targetIds);
        $mediaIds = ArrayToolkit::column($docActivities, 'mediaId');
        try {
            $files = $this->getUploadFileService()->findFilesByIds(
                $mediaIds,
                $showCloud
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

    public function findWithoutCloudFiles($targetIds)
    {
        return $this->getDocActivityDao()->findByIds($targetIds);
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
        return $this->getBiz()->dao('Activity:DocActivityDao');
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
