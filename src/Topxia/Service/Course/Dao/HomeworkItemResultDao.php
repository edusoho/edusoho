<?php 

namespace Topxia\Service\Course\Dao;

interface HomeworkItemResultDao
{
	public function getItemResult($id);

	public function getItemResultByHomeworkIdAndStatus($homeworkId,$status);

	public function getItemResultByResultIdAndQuestionId($resultId,$quesitionId);

	public function addItemResult($itemResult);

	public function updateItemResult($id,$fields);

	public function findItemsResultsbyHomeworkId($homeworkId);

	public function findItemsResultsbyHomeworkIdAndUserId($homeworkId,$userId);
	
}