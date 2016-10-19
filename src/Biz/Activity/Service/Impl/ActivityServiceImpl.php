<?php

namespace Biz\Activity\Service\Impl;


use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Dao\Impl\ActivityDaoImpl;
use Biz\BaseService;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\AccessDeniedException;
use Biz\Activity\Model\ActivityBuilder;
use Biz\Activity\Event\EventBuilder;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Event\EventChain;
use Biz\Activity\Event\ActivityLearnLogEvent;

class ActivityServiceImpl extends BaseService implements ActivityService
{
    public function getActivity($id)
    {
        return $this->getActivityDao()->get($id);
    }

    public function trigger($id, $eventName, $data)
    {
        $activity = $this->getActivity($id);

        if (in_array($eventName, array('start', 'doing', 'finish'))) {
            $this->biz['dispatcher']->dispatch("activity.{$eventName}", new Event($activity, $data));
        }

        $eventChain = new EventChain();
        $eventName  = $activity['mediaType'].'.'.$eventName;
        $event      = ActivityBuilder::build($this->biz)
            ->type($activity['mediaType'])
            ->done()
            ->getEvent($eventName);

        if (!empty($event)) {
            $eventChain->add($event);
        }

        $logEvent = EventBuilder::build($this->biz)
            ->setEventClass(ActivityLearnLogEvent::class)
            ->setName($eventName)
            ->done();
        $eventChain->add($logEvent);
        $eventChain->fire($activity, $data);
    }

    public function createActivity($activity)
    {
        if ($this->invalidActivity($activity)) {
            throw new \InvalidArgumentException('activity is invalid');
        }

        if (!$this->canManageCourse($activity['fromCourseId'])) {
            throw new AccessDeniedException();
        }

        if (!$this->canManageCourseSet($activity['fromCourseSetId'])) {
            throw new AccessDeniedException();
        }

        $activityModel = ActivityBuilder::build($this->biz)
            ->type($activity['mediaType'])
            ->done();
        $activityModel->create($activity);

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

        return $this->getActivityDao()->create($fields);
    }

    public function updateActivity($id, $fields)
    {
        $savedActivity = $this->getActivity($id);

        if (!$this->canManageCourse($savedActivity['fromCourseId'])) {
            throw new AccessDeniedException();
        }

        $activityModel = ActivityBuilder::build($this->biz)
            ->type($savedActivity['mediaType'])
            ->done();

        $activityModel->update($savedActivity['mediaId'], $fields);

        return $this->getActivityDao()->update($id, $fields);
    }

    public function getActivityModel($type)
    {
        return ActivityBuilder::build($this->biz)->type($type)->done();
    }

    public function deleteActivity($id)
    {
        $activity = $this->getActivity($id);

        if (!$this->canManageCourse($activity['fromCourseId'])) {
            throw new AccessDeniedException();
        }

        $activityModel = ActivityBuilder::build($this->biz)
            ->type($activity['mediaType'])
            ->done();

        $activityModel->delete($activity['mediaId']);

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
        return array(
            'text' => array(
                'name' => '图文'
            )
        );
    }
}
