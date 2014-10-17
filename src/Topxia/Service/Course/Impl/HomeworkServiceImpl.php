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

	public function findHomeworksByCreatedUserId($userId)
	{
		return $this->getHomeworkDao()->findHomeworksByCreatedUserId($userId);
	}

	public function getHomeworkByCourseIdAndLessonId($courseId, $lessonId)
	{
		return $this->getHomeworkDao()->getHomeworkByCourseIdAndLessonId($courseId, $lessonId);
	}

	public function getHomeworkResult($id)
	{
        return $this->getHomeworkResultDao()->getHomeworkResult($id);
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

		$fields = $this->filterHomeworkFields($fields,$mode = 'add');
        $fields['courseId'] = $courseId;
        $fields['lessonId'] = $lessonId;
        $excludeIds = explode(',',$excludeIds);
        $fields['itemCount'] = count($excludeIds);

        $homework = $this->getHomeworkDao()->addHomework($fields);
		$this->addHomeworkItems($homework['id'],$excludeIds);
		
		$this->getLogService()->info('homework','create','创建课程{$courseId}课时{$lessonId}的作业');
		
		return $homework;
	}

    public function updateHomework($id, $fields)
    {
    	$homework = $this->getHomework($id);

    	if (empty($homework)) {
    		throw $this->createServiceException('作业不存在，更新作业失败！');
    	}

    	$this->deleteHomeworkItemsByHomeworkId($homework['id']);

		$excludeIds = $fields['excludeIds'];

		if (empty($excludeIds)) {
			$this->createServiceException("题目不能为空，编辑作业失败！");
		}

		unset($fields['excludeIds']);

		$fields = $this->filterHomeworkFields($fields,$mode = 'edit');
		
		$excludeIds = explode(',',$excludeIds);
		$fields['itemCount'] = count($excludeIds);

		$homework = $this->getHomeworkDao()->updateHomework($id,$fields);

		$this->addHomeworkItems($homework['id'],$excludeIds);
		
		$this->getLogService()->info('homework','update','更新课程{$courseId}课时{$lessonId}的{$id}作业');
		
		return $homework;


    }

    public function removeHomework($id)
    {
    	$homework = $this->getHomework($id);

    	if (empty($homework)) {
    		throw $this->createServiceException('作业不存在，删除作业失败！');
    	}

    	$this->deleteHomeworkItemsByHomeworkId($homework['id']);

    	$this->getHomeworkDao()->deleteHomework($id);

    	return true;
    }

    public function showHomework($id)
    {
        $itemResults = $this->getTestpaperItemResultDao()->findTestResultsByTestpaperResultId($testpaperResultId);
        $itemResults = ArrayToolkit::index($itemResults, 'questionId');

        $testpaperResult = $this->getTestpaperResultDao()->getTestpaperResult($testpaperResultId);

        $items = $this->getTestpaperItems($testpaperResult['testId']);
        $items = ArrayToolkit::index($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));
        $questions = ArrayToolkit::index($questions, 'id');

        $questions = $this->completeQuestion($items, $questions);

        $formatItems = array();
        foreach ($items as $questionId => $item) {

            if (array_key_exists($questionId, $itemResults)){
                $questions[$questionId]['testResult'] = $itemResults[$questionId];
            }

            $items[$questionId]['question'] = $questions[$questionId];

            if ($item['parentId'] != 0) {
                if (!array_key_exists('items', $items[$item['parentId']])) {
                    $items[$item['parentId']]['items'] = array();
                }
                $items[$item['parentId']]['items'][$questionId] = $items[$questionId];
                $formatItems['material'][$item['parentId']]['items'][$item['seq']] = $items[$questionId];
                unset($items[$questionId]);
            } else {
                $formatItems[$item['questionType']][$item['questionId']] = $items[$questionId];
            }

        }

        if ($isAccuracy){
            $accuracy = $this->makeAccuracy($items);
        }

        ksort($formatItems);
        return array(
            'formatItems' => $formatItems,
            'accuracy' => $isAccuracy ? $accuracy : null
        );
    }

    public function startHomework($id)
    {
        $homework = $this->getHomeworkDao()->getHomework($id);

        if (empty($homework)) {
            throw $this->createServiceException('课时作业不存在！');
        }

        $course = $this->getCourseService()->getCourse($homework['courseId']);
        if (empty($course)) {
            throw $this->createServiceException('作业所属课程不存在！');
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);
        if (empty($lesson)) {
            throw $this->createServiceException('作业所属课时不存在！');
        }

        $user = $this->getCurrentUser();

        $homeworkResult = $this->getHomeworkResultDao()->getHomeworkResultByHomeworkIdAndUserId($id,$user->id);

        if (!empty($homeworkResult)) {
            throw $this->createServiceException('您已经做过该作业了！');
        }

        $result = $this->getHomeworkResultDao()->getHomeworkResultByHomeworkIdAndStatusAndUserId($id,$user->id, 'doing');
        if (empty($result)){
            $homeworkResult = array(
                'homeworkId' => $homework['id'],
                'courseId' => $homework['courseId'],
                'lessonId' =>  $homework['lessonId'],
                'userId' => $this->getCurrentUser()->id,
                'checkTeacherId' => $homework['createdUserId'],
                'status' => 'doing',
                'usedTime' => time(),
            );

            return $this->getHomeworkResultDao()->addHomeworkResult($homeworkResult);
        } else {
            return $result;
        }


    }

    public function checkHomework($id,$userId,$checkHomeworkData)
    {
        $homeworkResult = $this->getHomeworkResultDao()->getHomeworkResultByHomeworkIdAndUserId($id,$userId);

        if (empty($homeworkResult)) {
            throw $this->createServiceException();
        }

        $fields['status'] = 'finished';
        $fields['checkedTime'] = time();
        $this->getHomeworkResultDao()->updateHomeworkResult($homeworkResult['id'],$fields);

        foreach ($checkHomeworkData['questionIds'] as $key => $questionId) {
            if (!empty($checkHomeworkData['teacherSay'][$key])) {
                $this->getHomeworkItemResultDao()->updateHomeworkItemResult($id,$homeworkResult['id'],$questionId,array('teacherSay'=>$checkHomeworkData['teacherSay'][$key]));
            }
        }

        return true;
    }

    public function submitHomework($id,$homework)
    {
        $this->addHomeworkItemResult($id,$homework);
        //reviewing
        $rightItemCount = 0;

        $homeworkItemsRusults = $this->getHomeworkItemResultDao()->findHomeworkItemsResultsbyHomeworkId($id);

        foreach ($homeworkItemsRusults as $key => $homeworkItemRusult) {
            if ($homeworkItemRusult['status'] == 'right') {
               $rightItemCount++;
            }
        }

        $homeworkitemResult['commitStatus'] = 'committed';
        $homeworkitemResult['rightItemCount'] = $rightItemCount;
        $homeworkitemResult['status'] = 'reviewing';

        $homeworkResult = $this->getHomeworkResultDao()->getHomeworkResultByHomeworkIdAndUserId($id, $this->getCurrentUser()->id);

        $result = $this->getHomeworkResultDao()->updateHomeworkResult($homeworkResult['id'],$homeworkitemResult);

        return $result;
    }

    public function saveHomework($id,$homework)
    {
        $userId = $this->getCurrentUser()->id;
        $homeworkItemResults = $this->getHomeworkItemResultDao()->findHomeworkItemsResultsbyHomeworkIdAndUserId($id,$userId);
        if (empty($homeworkItemResults)) {
           $this->addHomeworkItemResult($id,$homework);
        }
        $homeworkResult = $this->getHomeworkResultDao()->getHomeworkResultByHomeworkIdAndUserId($id, $userId);
        foreach ($homework as $questionId => $value) {
            $answer = $value['answer'];
            if (count($answer) > 1) {
                $answer = implode(",", $answer);
            } else {
                $answer = $answer[0];
            }
            $this->getHomeworkItemResultDao()->updateHomeworkItemResult($id,$homeworkResult['id'],$questionId,array('answer'=>$answer));
        }
        return $homeworkResult;
    }

    public function deleteHomeworksByCourseId($courseId)
    {

    }

    public function getHomeworkResultByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId)
    {
        return $this->getHomeworkResultDao()->getHomeworkResultByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId);
    }

    public function getHomeworkResultByHomeworkId($homeworkId)
    {
        return $this->getHomeworkResultDao()->getHomeworkResultByHomeworkId($homeworkId);
    }

    public function getHomeworkResultByHomeworkIdAndUserId($homeworkId, $userId)
    {
    	return $this->getHomeworkResultDao()->getHomeworkResultByHomeworkIdAndUserId($homeworkId, $userId);
    }

    public function searchHomeworkResults($conditions, $orderBy, $start, $limit)
    {
    	return $this->getHomeworkResultDao()->searchHomeworkResults($conditions, $orderBy, $start, $limit);
    }

    public function searchHomeworkResultsCount($conditions)
    {
    	return $this->getHomeworkResultDao()->searchHomeworkResultsCount($conditions);
    }

    public function findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId)
    {
    	return $this->getHomeworkResultDao()->findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId);
    }

    public function findHomeworkResultsByHomeworkIds($homeworkIds)
    {
    	return $this->getHomeworkResultDao()->findHomeworkResultsByHomeworkIds($homeworkIds);
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

    public function getItemSetByHomeworkId($homeworkId)
    {
        $items = $this->getHomeworkItemDao()->findItemsByHomeworkId($homeworkId);
        $indexdItems = ArrayToolkit::index($items, 'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds(array_keys($indexdItems));

        $validQuestionIds = array();

        foreach ($indexdItems as $index => $item) {
   
            $item['question'] = empty($questions[$item['questionId']]) ? null : $questions[$item['questionId']];
            if (empty($item['parentId'])) {
                $indexdItems[$index] = $item;
                continue;
            }

            if (empty($indexdItems[$item['parentId']]['subItems'])) {
                $indexdItems[$item['parentId']]['subItems'] = array();
            }

            $indexdItems[$item['parentId']]['subItems'][] = $item;
            unset($indexdItems[$item['questionId']]);
        }

        $set = array(
            'items' => array_values($indexdItems),
            'questionIds' => array(),
            'total' => 0,
        );

        foreach ($set['items'] as $item) {
            if (!empty($item['subItems'])) {
                $set['total'] += count($item['subItems']);
                $set['questionIds'] = array_merge($set['questionIds'], ArrayToolkit::column($item['subItems'],'questionId'));
            } else {
                $set['total'] ++;
                $set['questionIds'][] = $item['questionId'];
            }
        }

        return $set;
    }

    public function getItemSetResultByHomeworkIdAndUserId($homeworkId,$userId)
    {
        $items = $this->getHomeworkItemDao()->findItemsByHomeworkId($homeworkId);
        $itemsResults = $this->getHomeworkItemResultDao()->findHomeworkItemsResultsbyHomeworkIdAndUserId($homeworkId,$userId);
        $indexdItems = ArrayToolkit::index($items, 'questionId');
        $indexdItemsResults = ArrayToolkit::index($itemsResults, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds(array_keys($indexdItems));

        $i = 0;
        $validQuestionIds = array();

        foreach ($indexdItems as $index => $item) {
   
            $item['question'] = empty($questions[$item['questionId']]) ? null : $questions[$item['questionId']];
            if (empty($item['parentId'])) {
                $indexdItems[$index] = $item;
                $indexdItems[$index]['itemResult'] = $indexdItemsResults[$index];
                $i = 0;
                continue;
            }

            if (empty($indexdItems[$item['parentId']]['subItems'])) {
                $indexdItems[$item['parentId']]['subItems'] = array();
                $i = 0;
            }

            $indexdItems[$item['parentId']]['subItems'][] = $item;
            $indexdItems[$item['parentId']]['subItems'][$i]['itemResult'] = $indexdItemsResults[$index];
            $i++;

            unset($indexdItems[$item['questionId']]);
        }

        $set = array(
            'items' => array_values($indexdItems),
            'questionIds' => array(),
            'total' => 0,
        );

        foreach ($set['items'] as $item) {
            if (!empty($item['subItems'])) {
                $set['total'] += count($item['subItems']);
                $set['questionIds'] = array_merge($set['questionIds'], ArrayToolkit::column($item['subItems'],'questionId'));
            } else {
                $set['total'] ++;
                $set['questionIds'][] = $item['questionId'];
            }
        }

        return $set;
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

    private function addHomeworkItemResult($id,$homework)
    {
        $homeworkResult = $this->getHomeworkResultByHomeworkIdAndUserId($id, $this->getCurrentUser()->id);
        $homeworkItems = $this->getHomeworkService()->findHomeworkItemsByHomeworkId($id);
        $itemResult = array();
        $homeworkitemResult = array();

        foreach ($homeworkItems as $key => $homeworkItem) {
            if (!empty($homework[$homeworkItem['questionId']])) {

                if (!empty($homework[$homeworkItem['questionId']]['answer'])) {

                    $answer = $homework[$homeworkItem['questionId']]['answer'];

                    if (count($answer)>1) {
                        $answer = implode(",", $answer);
                    } else {
                        $answer = $answer[0];
                    }

                    $answerArray = array($answer);
                    $result = $this->getQuestionService()->judgeQuestion($homeworkItem['questionId'], $answerArray);
                    $status = $result['status'];
                } else {
                    $answer = null;
                    $status = "noAnswer";
                }

            } else {
                $answer = null;
                $status = "noAnswer";

            }

            $itemResult['itemId'] = $homeworkItem['id'];
            $itemResult['homeworkId'] = $homeworkItem['homeworkId'];
            $itemResult['homeworkResultId'] = $homeworkResult['id'];
            $itemResult['questionId'] = $homeworkItem['questionId'];
            $itemResult['userId'] = $this->getCurrentUser()->id;
            $itemResult['status'] = $status;
            $itemResult['answer'] = $answer;

            $this->getHomeworkItemResultDao()->addHomeworkItemResult($itemResult);
        }
    }

	private function addHomeworkItems($homeworkId,$excludeIds)
	{
        $homeworkItems = array();
        $homeworkItemsSub = array();
        $includeItemsSubIds = array();
        $index = 1;

		foreach ($excludeIds as $key => $excludeId) {

            $questions = $this->getQuestionService()->findQuestionsByParentId($excludeId);

            $items['seq'] = $index;
            $items['questionId'] = $excludeId;
            $items['homeworkId'] = $homeworkId;
            $items['parentId'] = 0;
            $homeworkItems[] = $this->getHomeworkItemDao()->addItem($items);
           

            if (!empty($questions)) {
                foreach ($questions as $key => $question) {
                    $items['seq'] = $index;
                    $items['questionId'] = $question['id'];
                    $items['homeworkId'] = $homeworkId;
                    $items['parentId'] = $question['parentId'];
                    $homeworkItems[] = $this->getHomeworkItemDao()->addItem($items);
                    $index++;  
                }
                $index -= 1;
            }
             $index++;
        }
	}

    private function deleteHomeworkItemsByHomeworkId($homeworkId)
    {
    	$homeworkItems = $this->getHomeworkItemDao()->findItemsByHomeworkId($homeworkId);

    	foreach ($homeworkItems as $key => $homeworkItem) {
    		$this->getHomeworkItemDao()->deleteItem($homeworkItem['id']);
    	}

    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getHomeworkService()
    {
        return $this->createService('Course.HomeworkService');
    }

    private function getQuestionService()
    {
    	return $this->createService('Question.QuestionService');
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

    private function getHomeworkItemResultDao()
    {
        return $this->createDao('Course.HomeworkItemResultDao');
    }

    private function getHomeworkResultDao()
    {
    	return $this->createDao('Course.HomeworkResultDao');
    }

	private function filterHomeworkFields($fields,$mode)
	{
		$fields['description'] = $fields['description'];

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