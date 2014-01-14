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

    public function createUpdateTestPaper($id, $testPaper);

    public function updateTestPaper($id, $testPaper);

    public function deleteTestPaper($id);

    public function findTestPapersByTarget($targetType, $targetId, $start, $limit);
    
    public function searchTestPaper(array $conditions, array $orderBy, $start, $limit);

    public function searchTestPaperCount(array $conditions);
    
    /**
     * 
     *  test_item
     * 
     */

    public function getTestItem($id);

    public function createItem($testId, $questionId);

    public function createItems($testId, $field);

    public function updateItem($id, $fields);

    public function updateItems($testId, $field);
    
    public function deleteItem($id);
    
    public function findItemsByTestPaperId($testPaperId);

    public function buildTestPaper($testPaperId, $options, $builder);

}