<?php

namespace Activity\Service\Activity\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Activity\Service\Activity\ActivityService;
use Activity\Service\Activity\ActivityProcessorFactory;

class ActivityServiceImpl extends BaseService implements ActivityService
{
    public function getActivity($id)
    {
    }

    public function createActivity($activity)
    {
        if ($this->invalidActivity($activity)) {
            throw new \InvalidArgumentException('activity is invalid');
        }

        if (!$this->canCourseManage($activity['courseId'])) {
            throw $this->createAccessDeniedException();
        }

        $processor = ActivityProcessorFactory::getActivityProcessor($activity['mediaType']);
        $media     = $processor->create($activity);

        $fields = ArrayToolkit::parts($activity, array(
            'title',
            'desc',
            'mediaId',
            'mediaType',
            'content',
            'length',
            'fromCourseId',
            'fromCourseSetId',
            'fromUserId',
            'startTime',
            'endTime'
        ));

        if (!empty($media)) {
            $fields['mediaId'] = $media['id'];
        }

        $fields['fromUserId'] = $this->getCurrentUser()->getId();

        return $this->getActivityDao()->add($fields);
    }

    public function updateActivity($id, $fields)
    {
    }

    public function deleteActivity($id)
    {
        $activity = $this->getActivity($id);

        $processor = ActivityProcessorFactory::getActivityProcessor($activity['mediaType']);
        $processor->delete($activity['mediaId']);

        return $this->getActivityDao()->delete($id);
    }

    protected function getActivityDao()
    {
        return $this->getServiceKernel()->createDao('Activity:Activity.ActivityDao');
    }

    protected function canCourseManage($courseId)
    {
        return true;
    }

    protected function invalidActivity($activity)
    {
        return ArrayToolkit::requireds($activity, array(
            'title',
            'mediaType',
            'fromCourseId',
            'fromCourseSetId'
        ));
    }
}
