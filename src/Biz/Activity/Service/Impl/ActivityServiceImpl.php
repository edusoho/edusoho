<?php

namespace Biz\Activity\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Codeages\Biz\Framework\Event\Event;
use Biz\Activity\Config\ActivityFactory;
use Biz\Activity\Service\ActivityService;
use Topxia\Common\Exception\AccessDeniedException;
use Biz\Activity\Listener\ActivityLearnLogListener;
use Topxia\Common\Exception\InvalidArgumentException;

class ActivityServiceImpl extends BaseService implements ActivityService
{
    public function getActivity($id)
    {
        $activity = $this->getActivityDao()->get($id);

        if (!empty($activity['mediaId'])) {
            $activityConfig  = ActivityFactory::create($this->biz, $activity['mediaType']);
            $media           = $activityConfig->get($activity['mediaId']);
            $activity['ext'] = $media;
        }
        return $activity;
    }

    public function getActivities($ids)
    {
        return $this->getActivityDao()->findByIds($ids);
    }

    public function trigger($id, $eventName, $data = array())
    {
        $activity = $this->getActivity($id);

        if(empty($activity)){
            return;
        }

        if (in_array($eventName, array('start', 'doing', 'finish'))) {
            $this->biz['dispatcher']->dispatch("activity.{$eventName}", new Event($activity, $data));
        }

        $logListener    = new ActivityLearnLogListener($this->biz);

        $logData = $data;
        $logData['event'] = $activity['mediaType'] . '.' . $eventName;
        $logListener->handle($activity, $logData);

        $listeners   = array();
        $activityListener = ActivityFactory::create($this->biz, $activity['mediaType'])->getListener($eventName);
        if (!is_null($activityListener)) {
            $listeners[] = $activityListener;
        }

        foreach ($listeners as $listener) {
            $listener->handle($activity, $data);
        }
    }

    public function createActivity($fields)
    {
        if ($this->invalidActivity($fields)) {
            throw new InvalidArgumentException('activity is invalid');
        }

        if (!$this->canManageCourse($fields['fromCourseId'])) {
            throw new AccessDeniedException();
        }

        if (!$this->canManageCourseSet($fields['fromCourseSetId'])) {
            throw new AccessDeniedException();
        }

        $activityConfig = ActivityFactory::create($this->biz, $fields['mediaType']);
        $media          = $activityConfig->create($fields);

        if (!empty($media)) {
            $fields['mediaId'] = $media['id'];
        }

        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'remark',
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

        if (isset($fields['startTime']) && isset($fields['length'])) {
            $fields['endTime'] = $fields['startTime'] + $fields['length'] * 60;
        }

        $activity = $this->getActivityDao()->create($fields);

        $listener = $activityConfig->getListener('activity.created');
        if (!empty($listener)) {
            $listener->handle($activity, array());
        }

        return $activity;
    }

    public function updateActivity($id, $fields)
    {
        $savedActivity = $this->getActivity($id);

        if (!$this->canManageCourse($savedActivity['fromCourseId'])) {
            throw new AccessDeniedException();
        }

        $activityConfig = ActivityFactory::create($this->biz, $savedActivity['mediaType']);
        if (!empty($savedActivity['mediaId'])) {
            $activityConfig->update($savedActivity['mediaId'], $fields);
        }

        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'remark',
            'desc',
            'content',
            'length',
            'startTime',
            'endTime'
        ));

        if (isset($fields['startTime']) && isset($fields['length'])) {
            $fields['endTime'] = $fields['startTime'] + $fields['length'] * 60;
        }

        return $this->getActivityDao()->update($id, $fields);
    }

    public function getActivityConfig($type)
    {
        return ActivityFactory::create($this->biz, $type);
    }

    public function deleteActivity($id)
    {
        $activity = $this->getActivity($id);

        if (!$this->canManageCourse($activity['fromCourseId'])) {
            throw new AccessDeniedException();
        }

        $activityConfig = ActivityFactory::create($this->biz, $activity['mediaType']);

        $activityConfig->delete($activity['mediaId']);

        return $this->getActivityDao()->delete($id);
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
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
        ))
        ) {
            return true;
        }
        if (!in_array($activity['mediaType'], array_keys($this->getActivityTypes()))) {
            return true;
        }

        return false;
    }

    public function getActivityTypes()
    {
        return ActivityFactory::all($this->biz);
    }
}
