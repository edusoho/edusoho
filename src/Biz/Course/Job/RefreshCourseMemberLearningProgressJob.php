<?php

namespace Biz\Course\Job;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshCourseMemberLearningProgressJob extends AbstractJob
{
    public function execute()
    {
        try {
            $courseId = $this->args['courseId'];

            $memberUserIds = $this->getCourseMemberService()->findMemberUserIdsByCourseId($courseId);

            for ($i = 0; $i < count($memberUserIds)/100; $i++) {
                $userIds = array_slice($memberUserIds, 100*$i, 100);
                $marks = str_repeat('?,', count($userIds) - 1).'?';
                $this->biz['db']->executeUpdate("update course_member as cm set learnedNum=(select count(1) from course_task_result where userId = cm.userId and courseId = {$courseId} and status =\'finish\') where courseId={$courseId} and userId in ({$marks})", $userIds);
            }

//            foreach ($memberUserIds as $memberUserId) {
//                $this->getCourseService()->recountLearningData($courseId, $memberUserId);
//            }
        } catch (\Exception $e) {
            $message = $this->trace($e);
            $this->getLogService()->error('course', 'refresh_learning_progress', "重新刷新课程#{$courseId}下的学员的学习进度失败", array('error' => $message));
        }
    }

    private function trace($e, $seen=null)
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        if (!$seen) $seen = array();
        $trace  = $e->getTrace();
        $prev   = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen)) {
                $result[] = sprintf(' ... %d more', count($trace)+1);
                break;
            }
            $result[] = sprintf(' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line === null ? '' : $line);
            if (is_array($seen))
                $seen[] = "$file:$line";
            if (!count($trace))
                break;
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
        $result = join("\n", $result);
        if ($prev)
            $result  .= "\n" . jTraceEx($prev, $seen);

        return $result;
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
