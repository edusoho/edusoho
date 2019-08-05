<?php

namespace Biz\Testpaper\Service;

use Biz\Testpaper\Builder\TestpaperBuilder;
use Biz\System\Annotation\Log;

interface TestpaperService
{
    public function getTestpaper($id);

    public function getTestpaperByIdAndType($id, $type);

    public function findTestpapersByIdsAndType($ids, $type);

    /**
     * @param $fields
     *
     * @return mixed
     * @Log(module="course",action="add_testpaper")
     */
    public function createTestpaper($fields);

    public function batchCreateTestpaper($testpapers);

    public function updateTestpaper($id, $fields);

    /**
     * @param $id
     * @param bool $quietly
     *
     * @return mixed
     * @Log(module="course",action="delete_testpaper")
     */
    public function deleteTestpaper($id, $quietly = false);

    public function deleteTestpapers($ids);

    public function findTestpapersByIds($ids);

    public function getTestpaperByCopyIdAndCourseSetId($copyId, $courseSetId);

    public function searchTestpapers($conditions, $sort, $start, $limit);

    public function searchTestpaperCount($conditions);

    public function publishTestpaper($id);

    public function closeTestpaper($id);

    /**
     * testpaper_item.
     */
    public function getItem($id);

    public function createItem($fields);

    public function batchCreateItems($items);

    public function updateItem($id, $fields);

    public function deleteItem($id);

    public function deleteItemsByTestId($testpaperId);

    public function getItemsCountByParams(array $conditions, $groupBy = '');

    public function findItemsByTestId($testpaperId);

    public function findItemsByTestIds($testpaperIds);

    public function searchItems($conditions, $orderBy, $start, $limit);

    public function searchItemCount($conditions);

    public function getItemResult($id);

    public function createItemResult($fields);

    public function updateItemResult($itemResultId, $fields);

    public function findItemResultsByResultId($resultId, $showAttachment = false);

    public function getTestpaperResult($id);

    public function findTestpaperResultsByIds($ids);

    public function getUserUnfinishResult($testId, $courseId, $activityId, $type, $userId);

    public function getUserFinishedResult($testId, $courseId, $activityId, $type, $userId);

    public function getUserLatelyResultByTestId($userId, $testId, $courseId, $activityId, $type);

    public function findPaperResultsStatusNumGroupByStatus($testId, $activityId);

    public function addTestpaperResult($fields);

    public function updateTestpaperResult($id, $fields);

    public function searchTestpaperResultsCount($conditions);

    public function searchTestpaperResults($conditions, $sort, $start, $limit);

    public function searchTestpapersScore($conditions);

    public function buildTestpaper($fields, $type);

    public function canBuildTestpaper($type, $options);

    /**
     * 开始做试卷.
     */
    public function startTestpaper($id, $fields);

    public function finishTest($resultId, $formData);

    public function showTestpaperItems($testId, $resultId = 0);

    public function makeAccuracy($resultId);

    public function checkFinish($resultId, $fields);

    public function submitAnswers($id, $answers, $attachments);

    public function sumScore($itemResults);

    public function findAttachments($testId);

    public function canLookTestpaper($resultId);

    public function updateTestpaperItems($testpaperId, $items);

    /**
     * @param  $type
     *
     * @return TestpaperBuilder
     */
    public function getTestpaperBuilder($type);

    public function countQuestionTypes($testpaper, $items);

    /**
     * @param  $type
     *
     * @return
     * $usedTime
     * $firstScore
     * $maxScore
     */
    public function findTestResultsByTestpaperIdAndUserIds($userIds, $testpaperId);

    public function findExamFirstResults($testId, $type, $activityId);

    public function getCheckedQuestionTypeBySeq($testpaper);

    public function findResultsByTestIdAndActivityId($testId, $activityId);

    public function getNextReviewingResult($courseIds, $activityId, $type);

    public function buildExportTestpaperItems($testpaperId);

    public function importTestpaper($testpaperData, $token);
}
