<?php 

namespace Topxia\Service\Course\Dao;

interface HomeworkItemResultDao
{
	public function getHomeworkItemResult($id);

	public function getHomeworkItemResultByHomeworkIdAndStatus($homeworkId,$status);

	public function getHomeworkItemResultByHomeworkIdAndHomeworkResultIdAndQuestionId($homeworkId,$homeworkResultId,$questionId);

	public function addHomeworkItemResult($itemResult);

	public function updateHomeworkItemResult($homeworkId,$homeworkResultId,$questionId,$fields);

	public function findHomeworkItemsResultsbyHomeworkId($homeworkId);

	public function findHomeworkItemsResultsbyHomeworkIdAndUserId($homeworkId,$userId);
	
}