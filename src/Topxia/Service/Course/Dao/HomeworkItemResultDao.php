<?php 

namespace Topxia\Service\Course\Dao;

interface HomeworkItemResultDao
{
	public function getItemResult($id);

	public function getItemResultByHomeworkIdAndStatus($homeworkId,$status);

	public function getItemResultByResultIdAndQuesitionId($resultId,$quesitionId);

	public function addItemResult($itemResult);

	public function updateItemResult($id,$fields);

	public function findHomeworkItemsResultsbyHomeworkId($homeworkId);

	public function findHomeworkItemsResultsbyHomeworkIdAndUserId($homeworkId,$userId);
	
}