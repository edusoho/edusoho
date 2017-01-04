<?php


namespace Biz\Activity\Type;


use Biz\Activity\Config\Activity;
use Topxia\Common\ArrayToolkit;


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

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $doc = $this->getFlashActivityDao()->get($activity['mediaId']);
        if($doc['finishType'] == 'time') {
            $result = $this->getActivityLearnLogService()->sumLearnedTimeByActivityId($activityId);
            return $result > $doc['finishDetail'];
        }

        if($doc['finishType'] == 'click') {
            $result = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'doc.finish');
            return !empty($result);
        }
        return false;
    }

    public function update($targetId, $fields)
    {
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
        $activity =  $this->getDocActivityDao()->get($targetId);

        $activity['file'] = $this->getUploadFileService()->getFullFile($activity['mediaId']);
        return $activity;
    }

    protected function getDocActivityDao()
    {
        return $this->getBiz()->dao('Activity:DocActivityDao');
    }

    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service("Activity:ActivityLearnLogService");
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service("Activity:ActivityService");
    }
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
    
}