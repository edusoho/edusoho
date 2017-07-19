<?php

namespace Biz\Task\Job;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ExceptionPrintingToolkit;
use Biz\Common\Logger;
use Biz\Course\Dao\CourseDao;
use Biz\System\Service\LogService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\CourseStrategy;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CourseTaskDeleteSyncJob extends AbstractJob
{
    public function execute()
    {
        try {

            $task = $this->getTaskService()->getTask($this->args['taskId']);
            $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);

            $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
            $copiedCourseMap = ArrayToolkit::index($copiedCourses, 'id');
            $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
            foreach ($copiedTasks as $ct) {
                $this->deleteTask($ct['id'], $copiedCourseMap[$ct['courseId']]);
            }

            $this->getLogService()->info(Logger::COURSE, Logger::ACTION_SYNC_WHEN_TASK_DELETE, 'course.log.task.delete.sync.success_tips', array('taskId' => $task['id']));
        } catch (\Exception $e) {
            $this->getLogService()->error(Logger::COURSE, Logger::ACTION_SYNC_WHEN_TASK_DELETE, 'course.log.task.delete.sync.fail_tips', ExceptionPrintingToolkit::printTraceAsArray($e));
        }
    }

    private function deleteTask($taskId, $course)
    {
        return  $this->createCourseStrategy($course)->deleteTask($this->getTaskDao()->get($taskId));
    }

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    private function createCourseStrategy($course)
    {
        return $this->biz->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
