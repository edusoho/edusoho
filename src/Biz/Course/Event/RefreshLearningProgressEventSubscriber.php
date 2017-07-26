<?php

namespace Biz\Course\Event;

use Biz\Course\Dao\CourseJobDao;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Dao\LearningDataAnalysisDao;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RefreshLearningProgressEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.update' => 'onTaskUpdate',
            'course.task.delete' => 'onTaskDelete',
            'course.join' => 'onStudentJoin',
        );
    }

    public function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();

        $this->updateCourseTaskState('deleteStateIsChange', $task, +1);
    }

    public function onTaskUpdate(Event $event)
    {
        $newTask = $event->getSubject();
        $oldTask = $event->getArguments();
        $isOptionalChange = isset($oldTask['isOptional']) && $newTask['isOptional'] != $oldTask['isOptional'];
        if ($isOptionalChange) {
            $this->updateCourseTaskState('isOptionalStateIsChange', $newTask, $newTask['isOptional'] == 1 ? +1 : -1);
        }
    }

    public function onStudentJoin(Event $event)
    {
        $args = $event->getArguments();
        $member = $args['member'];

        $countOfFinishResult = $this->getTaskResultService()->countTaskResults(array(
            'userId' => $member['userId'],
            'courseId' => $member['courseId'],
            'status' => 'finish',
        ));

        if ($countOfFinishResult > 0) {
            $this->getLearningDataAnalysisDao()->batchRefreshUserLearningData($member['courseId'], array($member['userId']));
            $this->getMemberDao()->update($member['id'], array('lastLearnTime' => time()));
        }
    }

    private function updateCourseTaskState($type, $task, $stateCount)
    {
        $courseJob = $this->getCourseJobIfNotExistThenCreate($task['courseId']);

        if (!isset($courseJob['data'][$task['id']]) || !isset($courseJob['data'][$task['id']][$type])) {
            $courseJob['data'][$task['id']][$type] = 0;
        }

        $courseJob['data'][$task['id']][$type] += $stateCount;

        $this->getCourseJobDao()->update($courseJob['id'], array('data' => $courseJob['data']));
    }

    private function getCourseJobIfNotExistThenCreate($courseId)
    {
        $courseJob = $this->getCourseJobDao()->getByTypeAndCourseId('refresh_learning_progress', $courseId);

        if (!$courseJob) {
            $courseJob = $this->getCourseJobDao()->create(array('courseId' => $courseId, 'type' => 'refresh_learning_progress'));
        }

        return $courseJob;
    }

    /**
     * @return CourseJobDao
     */
    private function getCourseJobDao()
    {
        return $this->getBiz()->dao('Course:CourseJobDao');
    }

    /**
     * @return LearningDataAnalysisDao
     */
    private function getLearningDataAnalysisDao()
    {
        return $this->getBiz()->dao('Course:LearningDataAnalysisDao');
    }

    /**
     * @return CourseMemberDao
     */
    private function getMemberDao()
    {
        return $this->getBiz()->dao('Course:CourseMemberDao');
    }

    /**
     * @return TaskResultService
     */
    private function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }
}
