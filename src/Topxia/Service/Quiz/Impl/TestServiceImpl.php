<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\TestService;
use Topxia\Common\ArrayToolkit;

class TestServiceImpl extends BaseService implements TestService
{
	public function getPaper($id)
    {
        $paper = $this->getTestPaperDao()->getPaper($id);
        return $this->getPaperImplementor($paper['paperType'])->getPaper($paper);
    }

    public function createPaper($paper)
    {
        $field = $this->filterPaperFields($paper);
        return $this->getTestPaperDao()->addPaper($field);
    }

    public function updatePaper($id, $paper)
    {
        $field = $this->filterPaperFields($paper);
        $field['updatedTime'] = time();
        return $this->getPaperImplementor($paper['type'])->updatePaper($id, $paper, $field);  
    }

    public function deletePaper($id)
    {
        $paper = $this->getTestPaperDao()->getPaper($id);
        if (empty($paper)) {
            throw $this->createNotFoundException();
        }
        $this->getTestPaperDao()->deletePaper($id);

        $this->getTestPaperDao()->deletePapersByParentId($id);
        $this->getQuizPaperChoiceDao()->deleteChoicesByPaperIds(array($id));
    }

    public function createItem($testId, $questionId)
    {
    	$question = $this->getQuestionService()->getQuestion($questionId);
    	if(empty($question)){
    		return array();
    	}
    	$field = array();
        $field['testId'] = $testId;
        $field['seq'] = $this->getTestItemDao()->getItemsCountByTestId($testId)+1;
        $field['questionId'] = $question['id'];
        $field['questionType'] = $question['questionType'];
        $field['score'] = $question['score'];

        return $this->getTestItemDao()->addItem($field);
    }

    public function createItemsByPaper($field, $testId, $courseId)
    {
        $itemCount = $field['itemCount'];
        $itemScore = $field['itemScore'];

        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        $conditions['target']['course'] = $courseId;
        if (!empty($lessons)){
            $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');;
        }

        //查询各类型设置的数量的题目,
        $questions = array();
        foreach ($itemCount as $key => $count) {
            if($count == 0){
                continue;
            }
            $conditions['questionType'] = $key;
            $questions = array_merge($questions,$this->getQuestionService()->searchQuestion($conditions,array('createdTime' ,'DESC'),0,$count));
        }

        //循环题目(question),取出对应的item数据.顺序固定
        $items = $field = array();
        $seq = 1;
        foreach ($questions as $question) {
            $field['testId'] = $testId;
            $field['seq'] = $seq;
            $field['questionId'] = $question['id'];
            $field['questionType'] = '\''.$question['questionType'].'\'';
            $field['score'] = $itemScore[$question['questionType']]==0?$question['score']:$question['questionType'];
            $items[] = '('.implode(' , ', $field).')';
            $seq ++;
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
        $field['testId'] = $item['testId'];
        $field['seq'] = $item['seq'];
        $field['questionId'] = $question['id'];
        $field['questionType'] = $question['questionType'];
        $field['score'] = $question['score'];
        return $this->getTestItemDao()->updateItem($field);  
    }

    public function deleteItem($id)
    {
        $item = $this->getTestItemDao()->getItem($id);
        if (empty($item)) {
            throw $this->createNotFoundException();
        }
        $this->getTestItemDao()->deleteItem($id);
    }

    public function findPapersByCourseIds(array $id){
        return $this->getQuizPaperCategoryDao() -> findCategorysByCourseIds($id);
    }

    private function filterPaperFields($paper)
    {
        if(!ArrayToolkit::requireds($paper, array('name', 'itemCount', 'itemScore', 'target'))){
        	throw $this->createServiceException('缺少必要字段！');
        }

        $diff = array_diff(array_keys($paper['itemCount']), array_keys($paper['itemScore']));
        if (!empty($diff)) {
            throw $this->createServiceException('itemCount itemScore参数不正确');
        }

        list($targetType, $targetId) = explode('-', $paper['target']);

		if(empty($targetId)){
			throw $this->createNotFoundException('target 参数不正确');
		}
		if (!in_array($targetType, array('course','subject','unit','lesson'))) {
            throw $this->createServiceException("target 参数不正确");
        }

        $field = array();

        $field['name'] = $paper['name'];
        $field['targetId'] = $targetId;
        $field['targetType'] = $targetType;
		$field['description']   = empty($paper['description'])? '' :$paper['description'];
		$field['limitedTime']   = empty($paper['limitedTime'])? 0 :$paper['limitedTime'];;
		$field['createdUserId'] = $this->getCurrentUser()->id;
		$field['createdTime']   = time();

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



}
