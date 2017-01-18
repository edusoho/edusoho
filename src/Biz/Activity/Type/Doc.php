<?php

namespace Biz\Activity\Type;

use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\DocActivityDao;
use Biz\File\Service\UploadFileService;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\ActivityLearnLogService;

class Doc extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '文档',
            'icon' => 'es-icon es-icon-description'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'AppBundle:Doc:create',
            'edit'   => 'AppBundle:Doc:edit',
            'show'   => 'AppBundle:Doc:show'
        );
    }

    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    public function create($fields)
    {
        $doc = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        $biz                  = $this->getBiz();
        $doc['createdUserId'] = $biz['user']['id'];
        $doc['createdTime']   = time();

        $doc = $this->getDocActivityDao()->create($doc);
        return $doc;
    }

    public function copy($activity, $config = array())
    {
        $biz    = $this->getBiz();
        $doc    = $this->getDocActivityDao()->get($activity['mediaId']);
        $newDoc = array(
            'mediaId'       => $doc['mediaId'],
            'finishType'    => $doc['finishType'],
            'finishDetail'  => $doc['finishDetail'],
            'createdUserId' => $biz['user']['id']
        );

        return $this->getDocActivityDao()->create($newDoc);
    }

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $doc      = $this->getDocActivityDao()->get($activity['mediaId']);
        if ($doc['finishType'] == 'time') {
            $result = $this->getActivityLearnLogService()->sumLearnedTimeByActivityId($activityId);
            return $result >= $doc['finishDetail'];
        }
        return false;
    }

    public function update($targetId, &$fields, $activity)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
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

    /**
     * @return DocActivityDao
     */
    protected function getDocActivityDao()
    {
        return $this->getBiz()->dao('Activity:DocActivityDao');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service("Activity:ActivityLearnLogService");
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service("Activity:ActivityService");
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
