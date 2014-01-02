<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\TestService;
use Topxia\Common\ArrayToolkit;

class TestServiceImpl extends BaseService implements TestService
{
	public function getTestPaper($id)
    {
        return $this->getTestPaperDao()->getTestPaper($id);
    }

    public function createTestPaper($testPaper)
    {
        $field = $this->filterTestPaperFields($testPaper);
        $field['createdUserId'] = $this->getCurrentUser()->id;
        $field['createdTime']   = time();
        return $this->getTestPaperDao()->addTestPaper($field);
    }

    public function updateTestPaper($id, $testPaper)
    {
        $field = $this->filterTestPaperFields($testPaper);
        return $this->getTestPaperDao()->updateTestPaper($id, $field);  
    }

    public function deleteTestPaper($id)
    {
        $testPaper = $this->getTestPaperDao()->getTestPaper($id);
        if (empty($testPaper)) {
            throw $this->createNotFoundException();
        }
        $this->getTestPaperDao()->deleteTestPaper($id);
        $this->getTestPaperDao()->deletePapersByParentId($id);
        $this->getQuizPaperChoiceDao()->deleteChoicesByPaperIds(array($id));
    }

    public function getTestItem($id)
    {
        return $this->getTestItemDao()->getItem($id);
    }

    public function createItem($testId, $questionId)
    {
    	$question = $this->getQuestionService()->getQuestion($questionId);
    	if(empty($question)){
    		return array();
    	}

    	$field = array();
        $field['testId'] = $testId;
        $field['questionId'] = $question['id'];
        $field['questionType'] = $question['questionType'];
        $field['score'] = $question['score'];

        if($question['parentId'] == '0'){
            $field['seq'] = $this->getTestItemDao()->getItemsCountByTestIdAndQuestionType($testId, $question['questionType'])+1;
        } else {
            $field['seq'] = $this->getTestItemDao()->getItemsCountByTestIdAndParentId($testId, $question['parentId'])+1;
        }

        return $this->getTestItemDao()->addItem($field);
    }

    public function createItemsByTestPaper($field, $testId, $courseId)
    {
        $itemCounts = $field['itemCounts'];
        $itemScores = $field['itemScores'];

        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        $conditions['target']['course'] = array($courseId);
        if (!empty($lessons)){
            $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');
        }

        $questions = $this->getQuestionService()->searchQuestion($conditions, array('createdTime' ,'DESC'), 0, $count);

        foreach ($itemCounts as $key => $count) {
            if($count == 0){
                continue;
            }
            $conditions['questionType'] = $key;

            $seq = 1;
            foreach ($questions as $question) {
                $field['testId'] = $testId;
                $field['seq'] = $seq;
                $field['questionId'] = $question['id'];
                $field['questionType'] = '\''.$question['questionType'].'\'';
                $field['parentId'] = $question['parentId'];
                $field['score'] = $itemScores[$question['questionType']]==0?$question['score']:$itemScores[$question['questionType']];
                $items[] = '('.implode(' , ', $field).')';
                $seq ++;
            }

            //如果是材料题取出子题
            if ($key == 'material'){
                foreach ($questions as $key => $result) {
                    $con['parentIds'][] = $result['id'];
                }
                if(!empty($con)){
                    $questions = $this->getQuestionService()->searchQuestion($con, array('createdTime' ,'DESC'), 0, 999);
                }

                //循环题目(question),取出对应的item数据
                $seq = 1;
                foreach ($questions as $question) {
                    $field['testId'] = $testId;
                    $field['seq'] = $seq;
                    $field['questionId'] = $question['id'];
                    $field['questionType'] = '\''.$question['questionType'].'\'';
                    $field['parentId'] = $question['parentId'];
                    $field['score'] = $itemScores[$question['questionType']]==0?$question['score']:$itemScores[$question['questionType']];
                    $items[] = '('.implode(' , ', $field).')';
                    $seq ++;
                }
            }
        }
        return empty($items) ? array() : $this->getTestItemDao()->addItems($items);
    }

    public function updateItem($id, $questionId)
    {
        $item = $this->getTestItemDao()->getItem($id);
        $question = $this->getQuestionService()->getQuestion($questionId);
    	if(empty($item) || empty($question)){
    		return array();
        }
        $field['questionId']   = $question['id'];
        $field['questionType'] = $question['questionType'];
        $field['parentId']     = $question['parentId'];
        return $this->getTestItemDao()->updateItem($id, $field);  
    }

    public function deleteItem($id)
    {
        $item = $this->getTestItemDao()->getItem($id);
        if(empty($item)){
            return false;
        }
        if($item['parentId'] != 0){
            $this->getTestItemDao()->deleteItemsByParentId($item['parentId']);
        }
        $this->getTestItemDao()->deleteItem($id);
    }

    public function findTestPapersByCourseIds(array $id)
    {
        return $this->getQuizPaperCategoryDao() -> findCategorysByCourseIds($id);
    }

    public function findItemsByTestPaperId($testPaperId)
    {
        return $this->getTestItemDao()->findItemsByTestPaperId($testPaperId);
    }

    public function findItemsByTestPaperIdAndQuestionType($testPaperId, $type)
    {
        if(count($type) != 2){
            throw $this->createServiceException('type参数不正确');
        }
        return $this->getTestItemDao()->findItemsByTestPaperIdAndQuestionType($testPaperId, $type);
    }




    public function showTest ($testId)
    {
        $items = $this->findItemsByTestPaperId($testId);
        //材料题的id
        $materialIds = $this->findMaterial($items);
        $materialQuestions = $this->getQuestionService()->findQuestionsByParentIds($materialIds);

        //题目ids 不包括材料题的子题目
        $questionIds = ArrayToolkit::column($items, 'questionId');

        //找出题目
        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);
        //加入材料题子题目
        $questions = array_merge($questions, $materialQuestions);     
        $questions = ArrayToolkit::index($questions, 'id');
        //找出选择题答案
        $questionIds = array_merge($questionIds, ArrayToolkit::column($materialQuestions, 'id'));
        $answers = $this->getQuestionService()->findChoicesByQuestionIds($questionIds);

        $questions = QuestionSerialize::unserializes($questions);

        return $this->makeTest($questions, $answers);
    }

    private function makeTest ($questions, $answers)
    {
        foreach ($answers as $key => $value) {
            if (!array_key_exists('choices', $questions[$value['questionId']])) {
                $questions[$value['questionId']]['choices'] = array();
            }
            array_push($questions[$value['questionId']]['choices'], $value);
        }

        return $this->makeMaterial($questions);
    }

    private function makeMaterial ($questions)
    {
        foreach ($questions as $key => $value) {
            if ($value['targetId'] == 0) {
                if (!array_key_exists('questions', $questions[$value['parentId']])) {
                    $questions[$value['parentId']]['questions'] = array();
                }
                $questions[$value['parentId']]['questions'][$value['id']] = $value;
                unset($questions[$value['id']]);
            }
        }

        return $questions;
    }

    private function findMaterial ($items)
    {
        foreach ($items as $key => $value) {
            if ($value['questionType'] != 'material') {
                unset($items[$key]);
            }
        }
        return ArrayToolkit::column($items, 'questionId');
    }

    public function submitTest ($answers, $testId)
    {
        if (!empty($answers)) {
            return array();
        }
        //过滤待补充
        $user = $this->getCurrentUser();

        //已经有记录的
        $itemResults = $this->filterTestAnswers($answers, $testId, $user['id']);
        $itemIdsOld = ArrayToolkit::index($itemResults, 'itemId');

        $answersOld = ArrayToolkit::parts($answers, array_keys($itemIdsOld));

        if (!empty($answersOld)) {
            $this->getDoTestDao()->updateItemResults($answersOld, $testId, $user['id']);
        }
        //还没记录的
        $itemIdsNew = array_diff(array_keys($answers), array_keys($itemIdsOld));

        $answersNew = ArrayToolkit::parts($answers, $itemIdsNew);

        if (!empty($answersNew)) {
            $this->getDoTestDao()->addItemResults($answersNew, $testId, $user['id']);
        }

        //测试数据
        return $this->filterTestAnswers($answers, $testId, $user['id']);

    }

    private function filterTestAnswers ($answers, $testId, $userId)
    {
        return $this->getDoTestDao()->findTestResultsByItemIdAndTestId(array_keys($answers), $testId, $userId);
    }



    private function filterTestPaperFields($testPaper)
    {
        if(!ArrayToolkit::requireds($testPaper, array('name', 'itemCounts', 'itemScores', 'target'))){

        	throw $this->createServiceException('缺少必要字段！');
        }

        $diff = array_diff(array_keys($testPaper['itemCounts']), array_keys($testPaper['itemScores']));
        if (!empty($diff)) {
            throw $this->createServiceException('itemCounts itemScores参数不正确');
        }

        $target = explode('-', $testPaper['target']);

		if(empty($target['1'])){
			throw $this->createNotFoundException('target 参数不正确');
		}
		if (!in_array($target['0'], array('course','subject','unit','lesson'))) {
            throw $this->createServiceException("target 参数不正确");
        }

        $field = array();

        $field['name']          = $testPaper['name'];
        $field['targetId']      = $target['1'];
        $field['targetType']    = $target['0'];
        $field['seq']           = implode(',',array_keys($testPaper['itemScores']));
        $field['description']   = empty($testPaper['description'])? '' :$testPaper['description'];
        $field['limitedTime']   = empty($testPaper['limitedTime'])? 0 :$testPaper['limitedTime'];
        $field['updatedUserId'] = $this->getCurrentUser()->id;
        $field['updatedTime']   = time();

        return $field;
    }



    private function getTestPaperDao(){
    	return $this->createDao('Quiz.TestPaperDao');
    }

	private function getTestItemDao(){
	    return $this->createDao('Quiz.TestItemDao');
	}

    private function getQuestionService()
    {
        return $this->createService('Quiz.QuestionService');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getDoTestDao()
    {
        return $this->createDao('Quiz.DoTestDao');
    }



}
