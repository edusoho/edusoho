<?php

namespace Biz\Activity\Service\Impl;


use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Event\ActivityLearnLogEvent;
use Biz\Activity\Event\EventBuilder;
use Biz\Activity\Event\EventChain;
use Biz\Activity\Model\ActivityBuilder;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\AccessDeniedException;
use Topxia\Common\Exception\InvalidArgumentException;

class ActivityServiceImpl extends BaseService implements ActivityService
{
    public function getActivity($id)
    {
        return $this->getActivityDao()->get($id);
    }

    public function trigger($id, $eventName, $data = array())
    {
        $activity = $this->getActivity($id);

        if (in_array($eventName, array('start', 'doing', 'finish'))) {
            $this->biz['dispatcher']->dispatch("activity.{$eventName}", new Event($activity, $data));
        }

        $eventChain = new EventChain();
        $eventName  = $activity['mediaType'] . '.' . $eventName;
        $event      = ActivityBuilder::build($this->biz)
            ->type($activity['mediaType'])
            ->done()
            ->getConfig()
            ->getEvent($eventName);

        if (!empty($event)) {
            $event->setSubject($activity)->setArguments($data);
            $eventChain->add($event);
        }

        $logEvent = EventBuilder::build($this->biz)
            ->setEventClass(ActivityLearnLogEvent::class)
            ->setName($eventName)
            ->setSubject($activity)
            ->setArguments($data)
            ->done();
        $eventChain->add($logEvent);
        $eventChain->fire();
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

        $activityModel = ActivityBuilder::build($this->biz)
            ->type($fields['mediaType'])
            ->done();
        $media         = $activityModel->create($fields);

        if (!empty($media)) {
            $fields['mediaId'] = $media['id'];
        }

        $fields = ArrayToolkit::parts($fields, array(
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

        $activity = $this->getActivityDao()->create($fields);

        $createdEvent = $activityModel->getConfig()->getEvent('activity.created');
        if(!empty($createdEvent)){
            $createdEvent->setSubject($activity);
            $createdEvent->trigger();
        }

        return $activity;
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
        return array(
            'text' => array(
                'name' => '图文'
            )
        );
    }
}
