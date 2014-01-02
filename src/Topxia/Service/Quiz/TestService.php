<?php
namespace Topxia\Service\Quiz;

interface TestService
{
	/**
     *        
     *  test_paper    
     * 
     */

    public function getTestPaper($id);

    public function createTestPaper($testPaper);

    public function updateTestPaper($id, $testPaper);

    public function deleteTestPaper($id);
    
    public function findTestPapersByCourseIds(array $id);

    public function searchTestPaper(array $conditions, array $orderBy, $start, $limit);

    public function searchTestPaperCount(array $conditions);
    
    /**
     * 
     *  test_item
     * 
     */

    public function getTestItem($id);

    public function createItem($testId, $questionId);

    public function createItems($testId, $ids, $scores);

    public function updateItem($id, $fields);

    public function deleteItem($id);
    
    public function findItemsByTestPaperId($testPaperId);

    public function findItemsByTestPaperIdAndQuestionType($testPaperId, $type);

}