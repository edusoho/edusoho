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

    public function searchTestPaper(array $conditions, array $orderBy, $start, $limit){
        return $this->getTestPaperDao()->searchTestPaper($conditions, $orderBy, $start, $limit);
    }

    public function searchTestPaperCount(array $conditions){
        return $this->getTestPaperDao()->searchTestPaperCount($conditions);
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
        $field['parentId'] = $question['parentId'];
        $field['score'] = $question['score'];

        $item = $this->getTestItemDao()->addItem($field);

        $this->sortTestItemsByTestId($testId);
        
        return $this->getTestItem($item['id']);
    }

    public function createItems($testId, $ids, $scores)
    {
        $diff = array_diff($ids, $scores);

        if(empty($diff)){
            throw $this->createServiceException('参数不正确');
        }

        foreach ($ids as $k => $id) {
            $question = $this->getQuestionService()->getQuestion($id);
            if(empty($question)){
                throw $this->createServiceException();
            }

            $field = array();
            $field['testId'] = $testId;
            $field['questionId'] = $question['id'];
            $field['questionType'] = $question['questionType'];
            $field['parentId'] = $question['parentId'];
            $field['score'] = (int) $scores[$k];

            $item = $this->getTestItemDao()->addItem($field);
        }

        $this->sortTestItemsByTestId($testId);
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

    public function sortTestItems($testId, array $itemIds)
    {
        $items = $this->findItemsByTestPaperId($testId);
        $testPaper = $this->getTestPaper($testId);

        $existedItemIds = array_keys($items);

        if (count($itemIds) != count($existedItemIds)) {
            throw $this->createServiceException('itemdIds参数不正确');
        }

        $diffItemIds = array_diff($itemIds, array_keys($items));
        if (!empty($diffItemIds)) {
            throw $this->createServiceException('itemdIds参数不正确');
        }

        $items = ArrayToolkit::index($items,'id');
        $seq = 1;
        foreach ($itemIds as $itemId) {
            $fields = array('seq' => $seq);
            $this->getTestItemDao()->updateItem($itemId, $fields);
            $seq ++;
        }
    }

    private function sortTestItemsByTestId($testId)
    {
        $items = $this->findItemsByTestPaperId($testId);
        $testPaper = $this->getTestPaper($testId);

        $groupItems = array();
        foreach ($items as $item) {
            if($item['parentId'] == 0){
                $groupItems[$item['questionType']][] = $item;
            } else {
                $groupItems[$item['parentId']][] = $item;
            }
        }

        $seqType =  explode(',', $testPaper['seq']);
        $seqNum = 1;

        foreach ($seqType as $type) {

            if (!empty($groupItems[$type])){
            
                foreach ($groupItems[$type] as $item) {

                    $fields = array('seq' => $seqNum);
                    $this->getTestItemDao()->updateItem($item['id'], $fields);

                    if($item['questionType'] == 'material' && !empty($groupItems[$item['questionId']])){
                        foreach ($groupItems[$item['questionId']] as $item) {
                            $fields = array('seq' => $seqNum);
                            $this->getTestItemDao()->updateItem($item['id'], $fields);
                            $seqNum ++;
                        }
                    }else{
                        $seqNum ++;
                    }

                    
                }


            }
        }
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

    public function testResults($testId, $userId = null)
    {
        if ($userId == null) {
            $userId = $this->getCurrentUser()->id;
        }
        $answers = $this->getDoTestDao()->findTestResultsByTestIdAndUserId($testId, $userId);

        $answers = QuestionSerialize::unserializes($answers);

        $answers = ArrayToolkit::index($answers, 'itemId');

        $items = $this->findItemsByTestPaperId($testId);

        $materialIds = $this->findMaterial($items);
        $materialQuestions = $this->getQuestionService()->findQuestionsByParentIds($materialIds);

        $questionIds = ArrayToolkit::column($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $questions = array_merge($questions, $materialQuestions);

        $questions = QuestionSerialize::unserializes($questions);

        $questions = ArrayToolkit::index($questions, 'id');


        $results = $this->makeTestResults($answers, $questions);

        return $results;
    }

    private function makeTestResults ($answers, $questions)
    {
        $materials = $this->findMaterial($questions);
        $results = array();
        foreach ($questions as $key => $question) {
            if (empty($answers[$key])) {
                $question['result'] = 'noAnswer';
                $question['userAnswer'] = "";

                $results[$key] = $question;
                continue;
            }

            if (!in_array($question['questionType'], array('single_choice', 'choice', 'determine', 'fill', 'material'))){
                continue;
            }

            if ($question['questionType'] == 'single_choice' or $question['questionType'] == 'choice') {

                $diff = array_diff($question['answer'], $answers[$key]['answer']);

                if (count($question['answer']) == count($answers[$key]['answer']) && empty($diff)) {
                    $question['result'] = 'right';
                } else {
                    $question['result'] = 'wrong';
                }
            }

            if ($question['questionType'] == 'determine') {
                $diff = array_diff($question['answer'], $answers[$key]['answer']);

                if (count($question['answer']) == count($answers[$key]['answer']) && empty($diff)) {
                    $question['result'] = 'right';
                } else {
                    $question['result'] = 'wrong';
                }
            }

            if ($question['questionType'] == 'fill') {
                $right = 0;
                foreach ($question['answer'] as $k => $value) {
                    $value = explode('|', $value);
                    if (count($value) == 1) {
                        if ($value[0] == $answers[$key]['answer'][$k]) {
                            $right++;
                        }
                    } else {
                        foreach ($value as $v) {
                            if ($v == $answers[$key]['answer'][$k]) {
                                $right++;
                            }
                        }
                    }
                }
                $question['result'] = $right;
            }
            $question['userAnswer'] = $answers[$key]['answer'];

            if ($question['targetId'] == 0) {
                $results[$question['parentId']]['questions'][$key] = $question;
                
            } else {
                $results[$key] = $question;
            }

        }

        return $results;
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
        if (empty($answers)) {
            return array();
        }

        $answers = array_map(function($answer){
            return json_encode($answer);
        }, $answers);

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

        foreach ($testPaper['itemCounts'] as $key => $score) {
            if($score == 0)
                unset($testPaper['itemCounts'][$key]);
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
        $field['seq']           = implode(',',array_keys($testPaper['itemCounts']));
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
