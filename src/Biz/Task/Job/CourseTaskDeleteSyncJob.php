<?php

namespace Biz\Task\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\AppLoggerConstant;
use Biz\Course\Service\MemberService;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\Task\Strategy\CourseStrategy;
use Codeages\Biz\Framework\Event\Event;

class CourseTaskDeleteSyncJob extends AbstractSyncJob
{
    public function execute()
    {
        $taskId = $this->args['taskId'];
        $courseId = $this->args['courseId'];
        try {
            $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($courseId, 1);
            $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
            $copiedCourseMap = ArrayToolkit::index($copiedCourses, 'id');
            $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($taskId, $copiedCourseIds);
            foreach ($copiedTasks as $ct) {
                $this->deleteTask($ct['id'], $copiedCourseMap[$ct['courseId']]);
                $this->getCourseMemberService()->recountLearningDataByCourseId($ct['courseId']);
            }
            $this->getLogService()->info(AppLoggerConstant::COURSE, 'sync_when_task_delete', 'course.log.task.delete.sync.success_tips', ['taskId' => $taskId]);
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::COURSE, 'sync_when_task_delete', 'course.log.task.delete.sync.fail_tips', ['error' => $e->getMessage()]);
            $this->innodbTrxLog($e);
            if (!isset($this->args['repeat']) || $this->args['repeat'] < 5) {
                $this->getSchedulerService()->register(array(
                    'name' => "course_task_delete_sync_job_{$taskId}",
                    'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                    'expression' => time() + 120,
                    'misfire_policy' => 'executing',
                    'class' => 'Biz\Task\Job\CourseTaskDeleteSyncJob',
                    'args' => array('taskId' => $taskId, 'courseId' => $courseId, 'repeat' => (isset($this->args['repeat'])?$this->args['repeat']:0) + 1),
                ));
            }
            throw $e;
        }
    }

    private function deleteTask($taskId, $course)
    {
        $task = $this->getTaskDao()->get($taskId);
        $res = $this->createCourseStrategy($course)->deleteTask($task);
        $this->dispatchEvent('course.task.delete', new Event($task, ['user' => $this->biz['user']]));

        return $res;
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
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
}
