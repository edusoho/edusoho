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
        return $this->getActivityDao()->get($id);
    }

    public function createActivity($activity)
    {
        if ($this->invalidActivity($activity)) {
            throw new \InvalidArgumentException('activity is invalid');
        }

        if (!$this->canCourseManage($activity['fromCourseId'])) {
            throw $this->createAccessDeniedException();
        }

        $processor = ActivityProcessorFactory::getActivityProcessor($activity['mediaType']);
        if (!empty($processor)) {
            $media               = $processor->create($activity);
            $activity['mediaId'] = $media['id'];
        }

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

        $fields['fromUserId'] = $this->getCurrentUser()->getId();

        return $this->getActivityDao()->add($fields);
    }

    public function updateActivity($id, $fields)
    {
        $savedActivity = $this->getActivity($id);
        $processor     = ActivityProcessorFactory::getActivityProcessor($savedActivity['mediaType']);

        if (!empty($processor) && !empty($savedActivity['mediaId'])) {
            $media = $processor->update($savedActivity['mediaId'], $fields);
        }

        return $this->getActivityDao()->update($id, $fields);
    }

    public function deleteActivity($id)
    {
        $activity = $this->getActivity($id);

        $processor = ActivityProcessorFactory::getActivityProcessor($activity['mediaType']);
        if (!empty($processor) && !empty($savedActivity['mediaId'])) {
            $processor->delete($activity['mediaId']);
        }

        return $this->getActivityDao()->delete($id);
    }

    protected function getActivityDao()
    {
        return $this->createDao('Activity:Activity.ActivityDao');
    }

    protected function canCourseManage($courseId)
    {
        return true;
    }

    protected function invalidActivity($activity)
    {
        if (!ArrayToolkit::requireds($activity, array(
            'title',
            'mediaType',
            'fromCourseId',
            'fromCourseSetId'
        ))) {
            return true;
        }

        if (!in_array($activity['mediaType'], $this->getMediaTypes())) {
            return true;
        }

        return false;
    }

    protected function getMediaTypes()
    {
        return array('text');
    }
}
