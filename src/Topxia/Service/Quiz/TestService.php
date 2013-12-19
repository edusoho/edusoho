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

    /**
     * 
     *  test_item
     * 
     */

    public function createItem($testId, $questionId);

    public function createItemsByTestPaper($field, $testId, $courseId);

    public function updateItem($id, $fields);

    public function deleteItem($id);
    
    public function getItemsByTestPaperId($testPaperId);

}