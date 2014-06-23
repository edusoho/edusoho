<?php  
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\HomeworkService;
use Topxia\Common\ArrayToolkit;

class HomeworkServiceImpl extends BaseService implements HomeworkService
{

	public function getHomework($id)
	{
		return $this->getHomeworkDao()->getHomework($id);
	}

	public function findHomeworksByCourseIdAndLessonIds($courseId, $lessonIds)
	{
		$homeworks = $this->getHomeworkDao()->findHomeworkByCourseIdAndLessonIds($courseId, $lessonIds);
        return ArrayToolkit::index($homeworks, 'lessonId');
	}

	public function getHomeworkResult($id)
	{

	}

	public function searchHomeworks($conditions, $sort, $start, $limit)
	{

	}

	public function createHomework($courseId,$lessonId,$fields)
	{
		if(empty($fields)){
			$this->createServiceException("内容为空，创建作业失败！");
		}

		$course = $this->getCourseService()->getCourse($courseId);

		if (empty($course)) {
			throw $this->createServiceException('课程不存在，创建作业失败！');
		}

		$lesson = $this->getCourseService()->getCourseLesson($courseId,$lessonId);

		if (empty($lesson)) {
			throw $this->createServiceException('课时不存在，创建作业失败！');
		}

		$excludeIds = $fields['excludeIds'];

		if (empty($excludeIds)) {
			$this->createServiceException("题目不能为空，创建作业失败！");
		}

		unset($fields['excludeIds']);

		$excludeIds = explode(',',$excludeIds);

		$fields = $this->filterHomeworkFields($fields,$mode = 'add');
		$fields['courseId'] = $courseId;
		$fields['lessonId'] = $lessonId;
		$fields['itemCount'] = count($excludeIds);

		$homework = $this->getHomeworkDao()->addHomework($fields);

		//add items
		foreach ($excludeIds as $key => $excludeId) {
			$items['seq'] = $key+1;
			$items['questionId'] = $excludeId;
			$items['homeworkId'] = $homework['id'];
			$this->getHomeworkItemDao()->addItem($items);
		}
		
		$this->getLogService()->info('homework','create','创建课程{$courseId}课时{$lessonId}的作业');
		
		return true;
	}

    public function updateHomework($id, $fields)
    {
    	$homework = $this->getHomework($id);

    	if (empty($homework)) {
    		throw $this->createServiceException('作业不存在，更新作业失败！');
    	}

    	$homeworkItems = $this->getHomeworkItemDao()->findItemsByHomeworkId($homework['id']);

    	foreach ($homeworkItems as $key => $homeworkItem) {
    		$this->getHomeworkItemDao()->deleteItem($homeworkItem['id']);
    	}

		$excludeIds = $fields['excludeIds'];

		if (empty($excludeIds)) {
			$this->createServiceException("题目不能为空，编辑作业失败！");
		}

		unset($fields['excludeIds']);

		$excludeIds = explode(',',$excludeIds);

		$fields = $this->filterHomeworkFields($fields,$mode = 'edit');
		$fields['itemCount'] = count($excludeIds);

		$homework = $this->getHomeworkDao()->updateHomework($id,$fields);

		foreach ($excludeIds as $key => $excludeId) {
			$items['seq'] = $key+1;
			$items['questionId'] = $excludeId;
			$items['homeworkId'] = $homework['id'];
			$this->getHomeworkItemDao()->addItem($items);
		}
		
		$this->getLogService()->info('homework','update','更新课程{$courseId}课时{$lessonId}的{$id}作业');
		
		return true;


    }

    public function deleteHomework($id)
    {

    }

    public function deleteHomeworksByCourseId($courseId)
    {

    }

    public function searchHomeworkResults($conditions, $orderBy, $start, $limit)
    {
    	return $this->getHomeworkResultsDao()->searchHomeworkResults($conditions, $orderBy, $start, $limit);
    }

    public function findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId)
    {
    	return $this->getHomeworkResultsDao()->findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId);
    }

    public function findHomeworkResultsByStatusAndCheckTeacherId($checkTeacherId, $status)
    {

    }

    public function findHomeworkResultsByCourseIdAndStatusAndCheckTeacherId($courseId,$checkTeacherId, $status)
    {

    }

    public function findHomeworkResultsByStatusAndStatusAndUserId($userId, $status)
    {

    }

    public function findAllHomeworksByCourseId ($courseId)
    {

    }

    public function findHomeworkItemsByHomeworkId($homeworkId)
    {
		return $this->getHomeworkItemDao()->findItemsByHomeworkId($homeworkId);
    }

    public function updateHomeworkItems($homeworkId, $items)
    {

    }

	public function createHomeworkItems($homeworkId, $items)
	{
		$homework = $this->getHomework($homeworkId);

		if (empty($homework)) {
			throw $this->createServiceException('课时作业不存在，创建作业题目失败！');
		}

		$this->getHomeworkItemDao()->addItem($items);
	}

    private function getCourseService()
    {
    	return $this->createService('Course.CourseService');
    }

    private function getLogService()
    {
    	return $this->createService('System.LogService');
    }

    private function getHomeworkDao()
    {
    	return $this->createDao('Course.HomeworkDao');
    }

	private function getHomeworkItemDao()
    {
    	return $this->createDao('Course.HomeworkItemDao');
    }

    private function getHomeworkResultsDao()
    {
    	return $this->createDao('Course.HomeworkResultsDao');
    }

	private function filterHomeworkFields($fields,$mode)
	{
		$fields['description'] = $fields['description'];
		$fields['completeLimit'] = $fields['completeLimit'];

		if ($mode == 'add') {
			$fields['createdUserId'] = $this->getCurrentUser()->id;
			$fields['createdTime'] = time();
		}

		if ($mode == 'edit') {
			$fields['updatedUserId'] = $this->getCurrentUser()->id;
			$fields['updatedTime'] = time();
		}

		return $fields;
	}
}