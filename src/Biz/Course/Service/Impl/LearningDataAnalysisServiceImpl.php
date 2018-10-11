<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\CourseException;
use Biz\Course\MemberException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LearningDataAnalysisService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;
use Biz\Course\Dao\LearningDataAnalysisDao;

class LearningDataAnalysisServiceImpl extends BaseService implements LearningDataAnalysisService
{
    public function getUserLearningProgress($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $courseMember = $this->getMemberService()->getCourseMember($courseId, $userId);

        return $this->makeProgress($courseMember['learnedCompulsoryTaskNum'], $course['compulsoryTaskNum']);
    }

    public function makeProgress($learnedNum, $total)
    {
        $progress = array(
            'percent' => 0,
            'decimal' => 0,
            'finishedCount' => 0,
            'total' => $total,
        );

        $progress['finishedCount'] = $learnedNum > $progress['total'] ? $progress['total'] : $learnedNum;
        $progress['percent'] = $progress['finishedCount'] ? round($progress['finishedCount'] / $progress['total'], 2) * 100 : 0;
        $progress['decimal'] = $progress['finishedCount'] ? round($progress['finishedCount'] / $progress['total'], 2) : 0;
        $progress['percent'] = $progress['percent'] > 100 ? 100 : $progress['percent'];
        $progress['decimal'] = $progress['decimal'] > 1 ? 1 : $progress['decimal'];

        return $progress;
    }

    public function getUserLearningProgressByCourseIds($courseIds, $userId)
    {
        $statisticData = $this->getLearningDataAnalysisDao()->sumStatisticDataByCourseIdsAndUserId($courseIds, $userId);

        return $this->makeProgress($statisticData['learnedCompulsoryTaskNum'], $statisticData['compulsoryTaskNum']);
    }

    public function getUserLearningSchedule($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $member = $this->getMemberService()->getCourseMember($courseId, $userId);

        if (!$member) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }

        if (!$course['compulsoryTaskNum']) {
            return array(
                'taskCount' => 0,
                'progress' => 0,
                'taskResultCount' => 0,
                'toLearnTasks' => 0,
                'taskPerDay' => 0,
                'planStudyTaskCount' => 0,
                'planProgressProgress' => 0,
                'member' => $member,
            );
        }

        //学习进度
        $progress = $this->getUserLearningProgress($courseId, $userId);

        //待学习任务
        $toLearnTasks = $this->getTaskService()->findToLearnTasksByCourseId($course['id']);

        //任务式课程每日建议学习任务数
        $taskPerDay = $this->getFinishedTaskPerDay($course, $course['compulsoryTaskNum']);

        //计划应学数量
        $planStudyTaskCount = $this->getPlanStudyTaskCount($course, $member, $course['compulsoryTaskNum'], $taskPerDay);

        //计划进度
        $planProgressProgress = empty($taskCount) ? 0 : round($planStudyTaskCount / $taskCount, 2) * 100;

        return array(
            'taskCount' => $course['compulsoryTaskNum'],
            'progress' => $progress['percent'],
            'taskResultCount' => $progress['finishedCount'],
            'toLearnTasks' => $toLearnTasks,
            'taskPerDay' => $taskPerDay,
            'planStudyTaskCount' => $planStudyTaskCount,
            'planProgressProgress' => $planProgressProgress,
            'member' => $member,
        );
    }

    protected function getFinishedTaskPerDay($course, $taskNum)
    {
        //自由式不需要展示每日计划的学习任务数
        if ('freeMode' === $course['learnMode']) {
            return 0;
        }
        if ('days' === $course['expiryMode']) {
            $finishedTaskPerDay = empty($course['expiryDays']) ? 0 : $taskNum / $course['expiryDays'];
        } else {
            $diffDay = ($course['expiryEndDate'] - $course['expiryStartDate']) / (24 * 60 * 60);
            $finishedTaskPerDay = empty($diffDay) ? 0 : $taskNum / $diffDay;
        }

        return ceil($finishedTaskPerDay);
    }

    protected function getPlanStudyTaskCount($course, $member, $taskNum, $taskPerDay)
    {
        //自由式不需要展示应学任务数, 未设置学习有效期不需要展示应学任务数
        if ('freeMode' === $course['learnMode'] || empty($taskPerDay)) {
            return 0;
        }
        //当前时间减去课程
        //按天计算有效期， 当前的时间- 加入课程的时间 获得天数* 每天应学任务
        if ('days' === $course['expiryMode']) {
            $joinDays = (time() - $member['createdTime']) / (24 * 60 * 60);
        } else {
            //当前时间-减去课程有效期开始时间  获得天数 *应学任务数量
            $joinDays = (time() - $course['expiryStartDate']) / (24 * 60 * 60);
        }
        $joinDays = ceil($joinDays);

        return $taskPerDay * $joinDays >= $taskNum ? $taskNum : ceil($taskPerDay * $joinDays);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return MemberService
     */
    private function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return LearningDataAnalysisDao
     */
    private function getLearningDataAnalysisDao()
    {
        return $this->createDao('Course:LearningDataAnalysisDao');
    }
}
