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

    public function trigger($name, $activityId, $data)
    {
        $activity  = $this->getActivityDao()->get($id);
        $processor = ActivityProcessorFactory::getActivityProcessor($activity['mediaType']);
    }

    public function getActivityDetail($id)
    {
        $activity  = $this->getActivityDao()->get($id);
        $processor = ActivityProcessorFactory::getActivityProcessor($activity['mediaType']);
        if (empty($processor)) {
            $detail = array();
        } else {
            $detail = $processor->getDetailActivity($activity['mediaId']);
        }

        $typeConfig = ActivityProcessorFactory::getActivityTypeConfig($activity['mediaType']);

        return array($activity, $detail, $typeConfig);
    }

    public function createActivity($activity)
    {
        if ($this->invalidActivity($activity)) {
            throw new \InvalidArgumentException('activity is invalid');
        }

        if (!$this->canManageCourse($activity['fromCourseId'])) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->canManageCourseSet($activity['fromCourseSetId'])) {
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

        if (!$this->canManageCourse($savedActivity['fromCourseId'])) {
            throw $this->createAccessDeniedException();
        }

        $processor = ActivityProcessorFactory::getActivityProcessor($savedActivity['mediaType']);

        if (!empty($processor) && !empty($savedActivity['mediaId'])) {
            $media = $processor->update($savedActivity['mediaId'], $fields);
        }

        return $this->getActivityDao()->update($id, $fields);
    }

    public function deleteActivity($id)
    {
        $activity = $this->getActivity($id);

        if (!$this->canManageCourse($activity['fromCourseId'])) {
            throw $this->createAccessDeniedException();
        }

        $processor = ActivityProcessorFactory::getActivityProcessor($activity['mediaType']);
        if (!empty($processor) && !empty($savedActivity['mediaId'])) {
            $processor->delete($activity['mediaId']);
        }

        return $this->getActivityDao()->delete($id);
    }

    public function findActivitiesByCourseId($courseId)
    {
        return $this->getActivityDao()->findByCourseId($courseId);
    }

    protected function getActivityDao()
    {
        return $this->createDao('Activity:Activity.ActivityDao');
    }

    protected function canManageCourse($courseId)
    {
        return true;
    }

    protected function canManageCourseSet($fromCourseSetId)
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
        if (!in_array($activity['mediaType'], array_keys($this->getActivityTypes()))) {
            return true;
        }

        return false;
    }

    public function getActivityTypes()
    {
        return ActivityProcessorFactory::getActivityTypes();
    }
}
