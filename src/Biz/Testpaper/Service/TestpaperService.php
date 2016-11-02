<?php
namespace Biz\Testpaper\Service;

interface TestpaperService
{
    public function getTestpaper($id);

    public function findTestpapersByIds($ids);

    public function searchTestpapers($conditions, $sort, $start, $limit);

    public function searchTestpaperCount($conditions);

    /**
     * testpaper_item
     */

    public function getTestpaperItem($id);

    public function createTestpaperItem($fields);

    public function updateTestpaperItem($id, $fields);

    public function deleteTestpaperItem($id);

    /**
     * testpaper_result
     */

    public function getTestpaperResult($id);

    public function searchTestpaperResultsCount($conditions);

    public function searchTestpaperResults($conditions, $sort, $start, $limit);

    public function searchTestpapersScore($conditions);

    public function createTestpaper($fields);

    public function updateTestpaper($id, $fields);

    public function publishTestpaper($id);

    public function closeTestpaper($id);

    public function deleteTestpaper($id);

    public function deleteTestpaperItemByTestId($testpaperId);

    public function buildTestpaper($id, $options);

    public function canBuildTestpaper($builder, $options);

    public function canLookTestpaper($resultId);

    public function findTestpapersByCopyIdAndLockedTarget($copyId, $lockedTarget);

    public function findTestpaperResultsByUserId($id, $start, $limit);

    public function findTestpaperResultsCountByUserId($id);

    //将废弃
    public function findTestpaperResultByTestpaperIdAndUserIdAndActive($testpaperId, $userId);

    public function findTestpaperResultsByTestIdAndStatusAndUserId($testpaperId, $userId, array $status);

    public function findTestpaperResultsByStatusAndTestIds($ids, $status, $start, $limit);

    public function findTestpaperResultCountByStatusAndTestIds($ids, $status);

    public function findTestpaperResultsByStatusAndTeacherIds($ids, $status, $start, $limit);

    public function findTestpaperResultCountByStatusAndTeacherIds($ids, $status);

    public function findAllTestpapersByTarget($id);

    public function findAllTestpapersByTargets(array $ids);

    //new
    public function getUserDoingResult($testId, $courseId, $lessonId, $type, $userId);

    /**
     * 开始做试卷
     *
     * @param  [type] $id             [description]
     * @return [type] [description]
     */
    public function startTestpaper($id, $lessonId);

    public function previewTestpaper($testpaperId);

    public function showTestpaper($testpaperResultId, $isAccuracy = null);

    /**
     * [submitTestpaperAnswer description]
     * @param  [type] $testpaperId    [description]
     * @param  [type] $answers        [description]
     * @return [type] [description]
     */
    public function submitTestpaperAnswer($resultId, $answers);

    public function makeTestpaperResultFinish($id);

    public function finishTest($id, $userId, $usedTime);

    public function makeTeacherFinishTest($id, $paperId, $teacherId, $field);

    public function updateTestpaperResult($id, $usedTime);

    public function updateTestResultsByLessonId($lessonId, $fields);

    public function findTeacherTestpapersByTeacherId($teacherId);

    /**
     * 获取试卷的所有题目
     *
     * @param  integer $id                                                         试卷ID
     * @return array   试卷所有题目，包含item对应的question的信息
     */

    public function getTestpaperItems($testpaperId);

    public function updateTestpaperItems($testpaperId, $items);

    public function getItemsCountByParams($conditions, $groupBy = '');

}
