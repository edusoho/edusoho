<?php

namespace ApiBundle\Api\Resource\DashboardGraphicDatum;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;

class DashboardGraphicDatum extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $allMultiClasses = $this->getMultiClassService()->findAllMultiClass();
        $totalNewStudentNum = $this->getTotalNewStudentNum($allMultiClasses);
        $totalFinishedStudentNum = $this->getTotalFinishedStudentNum($allMultiClasses);
        $todayLiveData = $this->getTodayLiveData($allMultiClasses);
        $timeoutReviewNum = $this->getTimeoutReviewNum($allMultiClasses);
        $multiClassData = $this->getMultiClassData();
        $studyStudentData = $this->getStudyStudentData();

        return compact('totalNewStudentNum', 'totalFinishedStudentNum', 'todayLiveData', 'timeoutReviewNum', 'multiClassData', 'studyStudentData');
    }

    protected function getTotalNewStudentNum($allMultiClasses)
    {
        return $this->getCourseMemberService()->countMembers([
            'role' => 'student',
            'courseIds' => ArrayToolkit::column($allMultiClasses, 'courseId'),
            'startTimeGreaterThan' => strtotime('yesterday'),
            'startTimeLessThan' => strtotime(date('Y-m-d')) - 1,
        ]);
    }

    protected function getTotalFinishedStudentNum($allMultiClasses)
    {
        return $this->getCourseMemberService()->countMembers([
            'courseIds' => ArrayToolkit::column($allMultiClasses, 'courseId'),
            'finishedTime_GE' => strtotime('yesterday'),
            'finishedTime_LE' => strtotime(date('Y-m-d')) - 1,
        ]);
    }

    protected function getTodayLiveData($allMultiClasses)
    {
        $courses = $this->getCourseService()->searchWithJoinCourseSet(['ids' => ArrayToolkit::column($allMultiClasses, 'courseId'), 'courseSetStatus' => 'published'], [], 0, PHP_INT_MAX);
        $conditions = [
            'type' => 'live',
            'courseIds' => ArrayToolkit::column($courses, 'id'),
            'isLesson' => 1,
            'status' => 'published',
        ];

        $totalConditions = [
            'startTime_GE' => strtotime(date('Y-m-d')),
            'startTime_LE' => strtotime('tomorrow') - 1,
        ];
        $totalLives = $this->getTaskService()->countTasks(array_merge($conditions, $totalConditions));

        $overConditions = [
            'startTime_GE' => strtotime(date('Y-m-d')),
            'startTime_LE' => time(),
        ];
        $overLives = $this->getTaskService()->countTasks(array_merge($conditions, $overConditions));

        return ['totalLiveNum' => $totalLives, 'overLiveNum' => $overLives];
    }

    protected function getTimeoutReviewNum($allMultiClasses)
    {
        $courseIds = ArrayToolkit::column($allMultiClasses, 'courseId');
        $activities = $this->getActivityService()->findActivitiesByCourseIdsAndTypes($courseIds, ['homework', 'testpaper'], true);
        $answerSceneIds = [];
        foreach ($activities as $activity) {
            $answerSceneIds[] = $activity['ext']['answerSceneId'];
        }

        $reviewTimeLimit = $this->getSettingService()->node('multi_class.review_time_limit', 0);
        $timeoutReviewNum = 0;
        if ($reviewTimeLimit) {
            $timeoutReviewNum = $this->getAnswerRecordService()->count([
                'answer_scene_ids' => empty($answerSceneIds) ? [-1] : $answerSceneIds,
                'status' => 'reviewing',
                'endTime_LE' => time() - $reviewTimeLimit * 3600,
            ]);
        }

        return $timeoutReviewNum;
    }

    protected function getMultiClassData()
    {
        $startNum = $this->getMultiClassService()->countMultiClass(['startTimeLE' => time()]);
        $notStartNum = $this->getMultiClassService()->countMultiClass(['startTimeGT' => time()]);

        return ['startNum' => $startNum, 'notStartNum' => $notStartNum];
    }

    protected function getStudyStudentData()
    {
        $studyNum = $this->getMemberNum(['startTimeLE' => time()]);
        $notStudyNum = $this->getMemberNum(['startTimeGT' => time()]);

        return compact('studyNum', 'notStudyNum');
    }

    protected function getMemberNum($conditions)
    {
        $multiClasses = $this->getMultiClassService()->searchMultiClass($conditions, [], 0, PHP_INT_MAX);
        if (empty($multiClasses)) {
            return 0;
        }
        $courseIds = ArrayToolkit::column($multiClasses, 'courseId');

        return $this->getCourseMemberService()->countMembers(['courseIds' => $courseIds, 'role' => 'student']);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('course:CourseService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }
}
