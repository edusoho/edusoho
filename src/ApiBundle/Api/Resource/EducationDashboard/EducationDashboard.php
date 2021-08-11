<?php


namespace ApiBundle\Api\Resource\EducationDashboard;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;

class EducationDashboard extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $allMultiClasses = $this->getMultiClassService()->findAllMultiClass();
        $newStudentData = $this->getNewStudentsData($allMultiClasses);
        $totalFinishedStudentNum = $this->getTotalFinishedStudentNum($allMultiClasses);
        $todayLiveData = $this->getTodayLiveData($allMultiClasses);
        $reviewData = $this->getReviewData($allMultiClasses);
        $multiClassData = $this->getMultiClassData();
        $studyStudentData = $this->getStudyStudentData();
        $finishedRateList = $this->getFinishedRateList($allMultiClasses);
        $questionAnswerRateList = $this->getQuestionAnswerRateList($allMultiClasses);

        return compact('newStudentData', 'totalFinishedStudentNum', 'todayLiveData', 'reviewData', 'multiClassData', 'studyStudentData', 'finishedRateList', 'questionAnswerRateList');
    }

    protected function getNewStudentsData($allMultiClasses)
    {
        $conditions = [
            'multiClassIds' => ArrayToolkit::column($allMultiClasses, 'id'),
            'startTimeGreaterThan' => strtotime(date('Y-m-d')),
            'startTimeLessThan' => strtotime('tomorrow'),
        ];
        $totalNewStudentNum = $this->getCourseMemberService()->countMembers($conditions);
        $newAscSortStudents = $this->getCourseMemberService()->countGroupByMultiClassId($conditions);
        $newDescSortStudents = $this->getCourseMemberService()->countGroupByMultiClassId($conditions, 'DESC');
        $newAscSortStudents = $this->filterStudentNum($newAscSortStudents, $allMultiClasses);
        $newDescSortStudents = $this->filterStudentNum($newDescSortStudents, $allMultiClasses);

        return [
            'totalNum' => $totalNewStudentNum,
            'rankList' => ['ascSort' => $newAscSortStudents, 'descSort' => $newDescSortStudents]
        ];
    }

    protected function filterStudentNum($numList, $allMultiClasses)
    {
        foreach ($numList as &$list){
            $list['multiClass'] = isset($allMultiClasses[$list['multiClassId']]) ? $allMultiClasses[$list['multiClassId']]['title'] : '';
        }

        return $numList;
    }

    protected function getTotalFinishedStudentNum($allMultiClasses)
    {
        return $this->getCourseMemberService()->countMembers([
            'multiClassIds' => ArrayToolkit::column($allMultiClasses, 'id'),
            'finishedTime_GE' => strtotime(date('Y-m-d')),
            'finishedTime_LE' => strtotime('tomorrow') - 1,
        ]);
    }

    protected function getTodayLiveData($allMultiClasses)
    {
        $conditions = [
            'type' => 'live',
            'courseIds' => ArrayToolkit::column($allMultiClasses, 'courseId'),
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

    protected function getReviewData($allMultiClasses)
    {
        $courseIds = ArrayToolkit::column($allMultiClasses, 'courseId');
        $activities = $this->getActivityService()->findActivitiesByCourseIdsAndTypes($courseIds, ['homework', 'testpaper'], true);
        $answerSceneIds = [];
        $sceneIndexActivities = [];
        foreach ($activities as $activity) {
            $answerSceneIds[] = $activity['ext']['answerSceneId'];
            $sceneIndexActivities[$activity['ext']['answerSceneId']] = $activity;
        }

        $reviewTimeLimit = $this->getSettingService()->node('multi_class.review_time_limit', 24);
        $timeoutReviewNum =  $this->getAnswerRecordService()->count([
            'answer_scene_ids' => empty($answerSceneIds) ? [-1] : $answerSceneIds,
            'status' => 'reviewing',
            'endTime_LE' => time() - $reviewTimeLimit * 3600,
        ]);

        list($reviewRate, $descReviewRate) = $this->filterReviewRate($allMultiClasses, $answerSceneIds, $sceneIndexActivities);

        return ['timeoutReviewNum' => $timeoutReviewNum, 'reviewRateList' => ['ascSort' => $reviewRate, 'descSort' => $descReviewRate]];
    }

    protected function filterReviewRate($multiClasses, $answerSceneIds, $sceneIndexActivities)
    {
        $answerSceneIds = empty($answerSceneIds) ? [-1] : $answerSceneIds;
        $totalAnswerRecords = $this->getAnswerRecordService()->countGroupByAnswerSceneId(['answer_scene_ids' => $answerSceneIds]);
        $reviewedRecords = $this->getAnswerRecordService()->countGroupByAnswerSceneId(['answer_scene_ids' => $answerSceneIds, 'status' => 'finished']);
        $reviewRate = [];
        $multiClasses = ArrayToolkit::index($multiClasses, 'courseId');
        foreach ($totalAnswerRecords as $answerRecord) {
            $activity = isset($sceneIndexActivities[$answerRecord['answer_scene_id']]) ? $sceneIndexActivities[$answerRecord['answer_scene_id']] : [];
            $reviewRate[$answerRecord['answer_scene_id']]['multiClass'] = isset($multiClasses[$activity['courseId']]) ? $multiClasses[$activity['courseId']]['title'] : '';
            $reviewRate[$answerRecord['answer_scene_id']]['rate'] = $answerRecord['count'] && $reviewedRecords[$answerRecord['answer_scene_id']]['count'] ? round($reviewedRecords[$answerRecord['answer_scene_id']]['count'] / $answerRecord['count'], 2) : 0;
        }
        $descReviewRate = $reviewRate;
        asort($reviewRate);
        arsort($descReviewRate);

        return [$reviewRate, $descReviewRate];
    }

    protected function getMultiClassData()
    {
        $startNum = $this->getMultiClassService()->countMultiClass(['startTimeLE' => time(), 'endTimeGE' => time()]);
        $notStartNum = $this->getMultiClassService()->countMultiClass(['startTimeGT' => time()]);

        return ['startNum' => $startNum, 'notStartNum' => $notStartNum];
    }

    protected function getStudyStudentData()
    {
        $studyNum = $this->getMemberNum(['startTimeLE' => time(), 'endTimeGE' => time()]);
        $notStudyNum =  $this->getMemberNum(['startTimeGT' => time()]);

        return compact('studyNum', 'notStudyNum');
    }

    protected function getMemberNum($conditions)
    {
        $multiClasses = $this->getMultiClassService()->searchMultiClass($conditions, [], 0, PHP_INT_MAX);
        $courseIds = ArrayToolkit::column($multiClasses, 'courseId');
        return $this->getCourseMemberService()->countMembers(['courseIds' => $courseIds]);
    }

    protected function getFinishedRateList($allMultiClasses)
    {
        $conditions = [
            'multiClassIds' => ArrayToolkit::column($allMultiClasses, 'id'),
            'finishedTime_GE' => strtotime(date('Y-m-d')),
            'finishedTime_LE' => strtotime('tomorrow') - 1,
        ];
        $ascFinishedStudents = $this->getCourseMemberService()->countGroupByMultiClassId($conditions);
        $descFinishedStudents = $this->getCourseMemberService()->countGroupByMultiClassId($conditions, 'DESC');
        $courseIds = ArrayToolkit::column($allMultiClasses, 'courseId');
        $ascFinishedStudents = $this->filterRateList($allMultiClasses, $ascFinishedStudents, $courseIds);
        $descFinishedStudents = $this->filterRateList($allMultiClasses, $descFinishedStudents, $courseIds);

        return ['ascSort' => $ascFinishedStudents, 'descSort' => $descFinishedStudents];
    }

    protected function filterRateList($multiClasses, $finishedStudents, $courseIds)
    {
        $finishedRateList = [];
        $courses = ArrayToolkit::index($this->getCourseService()->findCoursesByIds($courseIds), 'multiClassId');
        $multiClasses = ArrayToolkit::index($multiClasses, 'id');
        foreach ($finishedStudents as $finishedStudent) {
            $answerRate[$finishedStudent['multiClassId']]['multiClass'] = isset($multiClasses[$finishedStudent['multiClassId']]) ? $multiClasses[$finishedStudent['multiClassId']]['title'] : '';
            $finishedRateList[$finishedStudent['multiClassId']]['rate'] = $finishedStudent['count'] && $courses[$finishedStudent['multiClassId']] ? round($finishedStudent['count'] / $courses[$finishedStudent['multiClassId']]['studentNum'], 2) : 0;
        }

        return $finishedRateList;
    }

    protected function getQuestionAnswerRateList($allMultiClasses)
    {
        $courseIds = ArrayToolkit::column($allMultiClasses, 'courseId');
        $conditions = [
            'courseIds' => $courseIds,
            'postNumLargerThan' => 0
        ];
        $ascAnsweredThread = $this->getThreadService()->countThreadsGroupedByCourseId($conditions);
        $descAnsweredThread = $this->getThreadService()->countThreadsGroupedByCourseId($conditions, 'DESC');
        $ascAnsweredThread = $this->filterAnswerRate($allMultiClasses, $ascAnsweredThread, $courseIds);
        $descAnsweredThread = $this->filterAnswerRate($allMultiClasses, $descAnsweredThread, $courseIds);

        return ['acrSort' => $ascAnsweredThread, 'descSort' => $descAnsweredThread];
    }

    protected function filterAnswerRate($multiClasses, $answeredThreads, $courseIds)
    {
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $answerRate = [];
        $answeredThreads = ArrayToolkit::index($answeredThreads, 'courseId');
        $multiClasses = ArrayToolkit::index($multiClasses, 'courseId');
        foreach ($courses as $course) {
            $answerRate[$course['id']]['multiClass'] = isset($multiClasses[$course['id']]) ? $multiClasses[$course['id']]['title'] : '';
            $answerRate[$course['id']]['rate'] = $course['questionNum'] && $answeredThreads[$course['id']]['count'] ? round($answeredThreads[$course['id']]['count'] / $course['questionNum'], 2) : 0;
        }

        return $answerRate;
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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->service('Course:ThreadService');
    }
}