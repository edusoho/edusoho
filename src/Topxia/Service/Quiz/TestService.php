<?php
namespace Topxia\Service\Quiz;

interface TestService
{
	/**
     *        
     *  test_paper    
     * 
     */

    public function getPaper($id);

    public function createPaper($paper);

    public function updatePaper($id, $paper);

    public function deletePaper($id);
    
    public function findPapersByCourseIds(array $id);

    /**
     * 
     *  test_item
     * 
     */

    public function createItem($testId, $questionId);

    public function createItemsByPaper($field, $testId, $courseId);

    public function updateItem($id, $fields);

    public function deleteItem($id);


}