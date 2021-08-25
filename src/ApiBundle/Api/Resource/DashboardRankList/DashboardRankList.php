<?php


namespace ApiBundle\Api\Resource\DashboardRankList;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\MultiClass\Service\MultiClassService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;

class DashboardRankList extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $startMultiClasses = $this->getMultiClassService()->searchMultiClass(['startTimeLE' => time()], [], 0, PHP_INT_MAX);
        $newStudentRankList = $this->getNewStudentsData();
        $reviewData = $this->getReviewData($startMultiClasses);
        $finishedRateList = $this->getFinishedRateList($startMultiClasses);
        $questionAnswerRateList = $this->getQuestionAnswerRateList($startMultiClasses);

        return compact('newStudentRankList', 'reviewData', 'finishedRateList', 'questionAnswerRateList');
    }

    protected function getNewStudentsData()
    {
        $allMultiClasses = $this->getMultiClassService()->findAllMultiClass();
        $conditions = [
            'role' => 'student',
            'courseIds' => ArrayToolkit::column($allMultiClasses, 'courseId'),
            'startTimeGreaterThan' => strtotime('yesterday'),
            'startTimeLessThan' => strtotime(date('Y-m-d')) - 1,
        ];
        $newSortStudents = $this->getCourseMemberService()->countGroupByCourseId($conditions);
        $newSortStudents = $this->filterStudentNum($newSortStudents, $allMultiClasses);

        return ['ascSort' => $this->sortRateList($newSortStudents, SORT_ASC, 'count'), 'descSort' => $this->sortRateList($newSortStudents, SORT_DESC, 'count')];
    }

    protected function filterStudentNum($numList, $allMultiClasses)
    {
        $studentNumList = [];
        $numList = ArrayToolkit::index($numList, 'courseId');
        foreach ($allMultiClasses as $multiClass){
            $studentNumList[$multiClass['courseId']]['multiClass'] = $multiClass['title'];
            $studentNumList[$multiClass['courseId']]['courseId'] = $multiClass['courseId'];
            $studentNumList[$multiClass['courseId']]['count'] = isset($numList[$multiClass['courseId']]) ? $numList[$multiClass['courseId']]['count'] : 0;
        }

        return $studentNumList;
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

        list($reviewRate, $descReviewRate) = $this->filterReviewRate($allMultiClasses, $answerSceneIds, $sceneIndexActivities);

        return ['ascSort' => $reviewRate, 'descSort' => $descReviewRate];
    }

    protected function filterReviewRate($multiClasses, $answerSceneIds, $sceneIndexActivities)
    {
        $answerSceneIds = empty($answerSceneIds) ? [-1] : $answerSceneIds;
        $totalAnswerRecords = $this->getAnswerRecordService()->countGroupByAnswerSceneId(['answer_scene_ids' => $answerSceneIds]);
        $reviewedRecords = $this->getAnswerRecordService()->countGroupByAnswerSceneId(['answer_scene_ids' => $answerSceneIds, 'status' => 'finished']);
        $totalAnswerRecords = $this->filterAnswerRecord($totalAnswerRecords, $sceneIndexActivities);
        $reviewedRecords = $this->filterAnswerRecord($reviewedRecords, $sceneIndexActivities);
        $reviewRate = [];
        foreach ($multiClasses as $multiClass) {
            $reviewRate[$multiClass['courseId']]['courseId'] = $multiClass['courseId'];
            $reviewRate[$multiClass['courseId']]['multiClass'] = $multiClass['title'];
            $reviewRate[$multiClass['courseId']]['rate'] =  isset($totalAnswerRecords[$multiClass['courseId']]) && isset($reviewedRecords[$multiClass['courseId']]) && $totalAnswerRecords[$multiClass['courseId']]['count'] && $reviewedRecords[$multiClass['courseId']]['count'] ? round($reviewedRecords[$multiClass['courseId']]['count'] / $totalAnswerRecords[$multiClass['courseId']]['count'], 2) : 0;
        }

        return [$this->sortRateList($reviewRate,SORT_ASC), $this->sortRateList($reviewRate, SORT_DESC)];
    }

    protected function filterAnswerRecord($answerRecords, $sceneIndexActivities)
    {
        foreach ($answerRecords as &$answerRecord) {
            $answerRecord['courseId'] = isset($sceneIndexActivities[$answerRecord['answer_scene_id']]) ? $sceneIndexActivities[$answerRecord['answer_scene_id']]['fromCourseId'] : [];
        }
        $answerRecords = ArrayToolkit::group($answerRecords, 'courseId');
        $records = [];
        foreach ($answerRecords as $courseId => $answerRecord) {
            $records[$courseId]['courseId'] = $courseId;
            $records[$courseId]['count'] = array_sum(ArrayToolkit::column($answerRecord, 'count'));
        }

        return $records;
    }

    protected function getFinishedRateList($allMultiClasses)
    {
        $conditions = [
            'courseIds' => ArrayToolkit::column($allMultiClasses, 'courseId'),
            'finishedTime_GE' => strtotime('yesterday'),
            'finishedTime_LE' => strtotime(date('Y-m-d')) - 1,
        ];
        $finishedStudents = $this->getCourseMemberService()->countGroupByCourseId($conditions);
        $finishedStudents = $this->filterRateList($allMultiClasses, $finishedStudents);

        return ['ascSort' => $this->sortRateList($finishedStudents, SORT_ASC), 'descSort' => $this->sortRateList($finishedStudents, SORT_DESC)];
    }

    protected function filterRateList($multiClasses, $finishedStudents)
    {
        $finishedRateList = [];
        $courses = $this->getCourseMemberService()->countGroupByCourseId(['role' => 'student', 'courseIds' => ArrayToolkit::column($multiClasses, 'courseId')]);
        $multiClasses = ArrayToolkit::index($multiClasses, 'courseId');
        $finishedStudents = ArrayToolkit::index($finishedStudents, 'courseId');
        $i = 0;
        foreach ($courses as $course) {
            $finishedRateList[$i]['courseId'] = $course['courseId'];
            $finishedRateList[$i]['multiClass'] = isset($multiClasses[$course['courseId']]) ? $multiClasses[$course['courseId']]['title'] : '';
            $finishedRateList[$i]['rate'] = $finishedStudents[$course['courseId']]['count'] && $course['count'] ? round($finishedStudents[$course['courseId']]['count'] / $course['count'], 2) : 0;
            $i++;
        }

        return $finishedRateList;
    }

    protected function getQuestionAnswerRateList($allMultiClasses)
    {
        $conditions = [
            'courseIds' => ArrayToolkit::column($allMultiClasses, 'courseId'),
            'postNumLargerThan' => 0
        ];
        $answeredThread = $this->getThreadService()->countThreadsGroupedByCourseId($conditions);
        $answeredThread = $this->filterAnswerRate($allMultiClasses, $answeredThread);

        return ['ascSort' => $this->sortRateList($answeredThread, SORT_ASC), 'descSort' => $this->sortRateList($answeredThread, SORT_DESC)];
    }

    protected function filterAnswerRate($multiClasses, $answeredThreads)
    {
        $courses = ArrayToolkit::index($this->getThreadService()->countThreadsGroupedByCourseId(['courseIds' =>ArrayToolkit::column($multiClasses, 'courseId')]), 'courseId');
        $answerRate = [];
        $answeredThreads = ArrayToolkit::index($answeredThreads, 'courseId');
        foreach ($multiClasses as $multiClass) {
            $answerRate[$multiClass['courseId']]['courseId'] = $multiClass['courseId'];
            $answerRate[$multiClass['courseId']]['multiClass'] = $multiClass['title'];
            $answerRate[$multiClass['courseId']]['rate'] = isset($courses[$multiClass['courseId']]) && isset($answeredThreads[$multiClass['courseId']]) && $courses[$multiClass['courseId']]['count'] && $answeredThreads[$multiClass['courseId']]['count'] ? round($answeredThreads[$multiClass['courseId']]['count'] / $courses[$multiClass['courseId']]['count'], 2) : 0;
        }

        return $answerRate;
    }

    protected function sortRateList($reviewRate, $order, $filed = 'rate')
    {
        $refer = [];
        foreach ($reviewRate as $key => $value) {
            $refer[$key] = $value[$filed];
        }
        array_multisort($refer, $order, SORT_NUMERIC, $reviewRate);

        return $reviewRate;
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
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->service('Course:ThreadService');
    }
}