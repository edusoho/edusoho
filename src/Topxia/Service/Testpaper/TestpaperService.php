<?php
namespace Topxia\Service\Testpaper;

interface TestpaperService
{
    public function getTestpaper($id);

    public function searchTestpapers($conditions, $sort, $start, $limit);

    public function searchTestpapersCount($conditions);

    public function publishTestpaper($id);

    public function closeTestpaper($id);

    public function deleteTestpaper($id);

    public function deleteTestpaperByIds(array $ids);

    public function buildTestpaper($id, $builder, $builderOptions);

    public function rebuildTestpaper($id, $builder, $builderOptions);


    public function findTestpaperResultsByTestpaperIdAndUserId($testpaperId, $userId);

    public function findTestPaperResultsByTestIdAndStatusAndUserId($testpaperId, $userId, array $status);


    /**
     * 开始做试卷
     * 
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function startTestpaper($id, $target);

    public function finishTestpaper($resultId);

    /**
     * [submitTestpaperAnswer description]
     * @param  [type] $testpaperId [description]
     * @param  [type] $answers     [description]
     * @return [type]              [description]
     */
    public function submitTestpaperAnswer($resultId, $answers);

    public function reviewTestpaper($resultId, $items, $remark = null);

    /**
     * 获取试卷的所有题目
     * 
     * @param  integer $id 试卷ID
     * @return array     试卷所有题目，包含item对应的question的信息
     */
    public function getTestpaperItems($testpaperId);

    public function addItem($testpaperId, $questionId, $afterItemId = null);

    public function replaceItem($testpaperId, $itemId, $questionId);

}