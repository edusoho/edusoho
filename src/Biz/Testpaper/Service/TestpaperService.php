<?php
namespace Biz\Testpaper\Service;

interface TestpaperService
{
    public function getTestpaper($id);

    public function createTestpaper($fields);

    public function updateTestpaper($id, $fields);

    public function deleteTestpaper($id);

    public function findTestpapersByIds($ids);

    public function searchTestpapers($conditions, $sort, $start, $limit);

    public function searchTestpaperCount($conditions);

    /**
     * testpaper_item
     */

    public function getItem($id);

    public function createItem($fields);

    public function updateItem($id, $fields);

    public function deleteItem($id);

    public function getItemsCountByParams(array $conditions, $groupBy = '');

    public function findItemsByTestId($testpaperId);

    public function searchItems($conditions, $orderBy, $start, $limit);

    public function searchItemCount($conditions);

    /*
     * testpaper_item_result
     */

    public function createItemResult($fields);

    public function updateItemResult($itemResultId, $fields);

    public function findItemResultsByResultId($resultId);

    /**
     * testpaper_result
     */

    public function getTestpaperResult($id);

    public function getUserUnfinishResult($testId, $courseId, $lessonId, $type, $userId);

    public function getUserLatelyResultByTestId($userId, $testId, $courseId, $lessonId, $type);

    public function addTestpaperResult($fields);

    public function updateTestpaperResult($id, $fields);

    public function searchTestpaperResultsCount($conditions);

    public function searchTestpaperResults($conditions, $sort, $start, $limit);

    public function searchTestpapersScore($conditions);

    public function buildTestpaper($fields, $type);

    public function publishTestpaper($id);

    public function closeTestpaper($id);

    public function deleteTestpaperItemByTestId($testpaperId);

    public function canBuildTestpaper($builder, $options);

    public function canLookTestpaper($resultId);

    /**
     * 开始做试卷
     *
     * @param  [type] $id             [description]
     * @return [type] [description]
     */
    public function startTestpaper($id, $lessonId);

    public function previewTestpaper($testpaperId);

    //public function showTestpaper($testpaperResultId, $isAccuracy = null);

    //public function submitAnswers($resultId, $answers);

    public function finishTest($resultId, $formData);

    public function makeTeacherFinishTest($id, $paperId, $teacherId, $field);

    public function updateTestpaperItems($testpaperId, $items);

    public function getTestpaperBuilder($type);

}
