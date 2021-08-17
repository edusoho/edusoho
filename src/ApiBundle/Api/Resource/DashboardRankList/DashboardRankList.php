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
        $allMultiClasses = $this->getMultiClassService()->findAllMultiClass();
        $newStudentRankList = $this->getNewStudentsData($allMultiClasses);
        $reviewData = $this->getReviewData($allMultiClasses);
        $finishedRateList = $this->getFinishedRateList($allMultiClasses);
        $questionAnswerRateList = $this->getQuestionAnswerRateList($allMultiClasses);

        return compact('newStudentRankList', 'reviewData', 'finishedRateList', 'questionAnswerRateList');
    }

    protected function getNewStudentsData($allMultiClasses)
    {
        $conditions = [
            'courseIds' => ArrayToolkit::column($allMultiClasses, 'courseId'),
            'startTimeGreaterThan' => strtotime('yesterday'),
            'startTimeLessThan' => strtotime(date('Y-m-d')) - 1,
        ];
        $newAscSortStudents = $this->getCourseMemberService()->countGroupByCourseId($conditions);
        $newDescSortStudents = $this->getCourseMemberService()->countGroupByCourseId($conditions, 'DESC');
        $newAscSortStudents = $this->filterStudentNum($newAscSortStudents, $allMultiClasses);
        $newDescSortStudents = $this->filterStudentNum($newDescSortStudents, $allMultiClasses);

        return ['ascSort' => $newAscSortStudents, 'descSort' => $newDescSortStudents];
    }

    protected function filterStudentNum($numList, $allMultiClasses)
    {
        $allMultiClasses = ArrayToolkit::index($allMultiClasses, 'courseId');
        foreach ($numList as &$list){
            $list['multiClass'] = isset($allMultiClasses[$list['courseId']]) ? $allMultiClasses[$list['courseId']]['title'] : '';
        }

        return $numList;
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
        $multiClasses = ArrayToolkit::index($multiClasses, 'courseId');
        foreach ($totalAnswerRecords as $answerRecord) {
            $reviewRate[$answerRecord['courseId']]['courseId'] = $answerRecord['courseId'];
            $reviewRate[$answerRecord['courseId']]['multiClass'] = isset($multiClasses[$answerRecord['courseId']]) ? $multiClasses[$answerRecord['courseId']]['title'] : '';
            $reviewRate[$answerRecord['courseId']]['rate'] = $answerRecord['count'] && $reviewedRecords[$answerRecord['courseId']]['count'] ? round($reviewedRecords[$answerRecord['courseId']]['count'] / $answerRecord['count'], 2) : 0;
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
        $ascFinishedStudents = $this->getCourseMemberService()->countGroupByCourseId($conditions);
        $descFinishedStudents = $this->getCourseMemberService()->countGroupByCourseId($conditions, 'DESC');
        $ascFinishedStudents = $this->filterRateList($allMultiClasses, $ascFinishedStudents);
        $descFinishedStudents = $this->filterRateList($allMultiClasses, $descFinishedStudents);

        return ['ascSort' => $ascFinishedStudents, 'descSort' => $descFinishedStudents];
    }

    protected function filterRateList($multiClasses, $finishedStudents)
    {
        $finishedRateList = [];
        $courses = $this->getCourseMemberService()->countGroupByCourseId(['courseIds' => ArrayToolkit::column($multiClasses, 'courseId')]);
        $multiClasses = ArrayToolkit::index($multiClasses, 'courseId');
        $finishedStudents = ArrayToolkit::index($finishedStudents, 'courseId');
        $i = 0;
        foreach ($courses as $course) {
            $finishedRateList[$i]['courseId'] = $course['courseId'];
            $finishedRateList[$i]['multiClass'] = isset($multiClasses[$course['courseId']]) ? $multiClasses[$course['courseId']]['title'] : '';
            $finishedRateList[$i]['rate'] = $finishedStudents[$course['courseId']]['count'] && $courses['count'] ? round($finishedStudents[$course['courseId']] / $courses['count'], 2) : 0;
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
        $courses = $this->getThreadService()->countThreadsGroupedByCourseId(['courseIds' =>ArrayToolkit::column($multiClasses, 'courseId')]);
        $answerRate = [];
        $answeredThreads = ArrayToolkit::index($answeredThreads, 'courseId');
        $multiClasses = ArrayToolkit::index($multiClasses, 'courseId');
        foreach ($courses as $course) {
            $answerRate[$course['courseId']]['courseId'] = $course['courseId'];
            $answerRate[$course['courseId']]['multiClass'] = isset($multiClasses[$course['courseId']]) ? $multiClasses[$course['courseId']]['title'] : '';
            $answerRate[$course['courseId']]['rate'] = $course['count'] && $answeredThreads[$course['courseId']]['count'] ? round($answeredThreads[$course['courseId']]['count'] / $course['count'], 2) : 0;
        }

        return $answerRate;
    }

    protected function sortRateList($reviewRate, $order)
    {
        $refer = [];
        foreach ($reviewRate as $key => $value) {
            $refer[$key] = $value['rate'];
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