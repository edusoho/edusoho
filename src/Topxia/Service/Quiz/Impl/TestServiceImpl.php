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
        return $this->getTestPaperDao()->addTestPaper($field);
    }

    public function updateTestPaper($id, $testPaper)
    {
        $field = $this->filterTestPaperFields($testPaper);
        $field['updatedTime'] = time();
        return $this->getPaperImplementor($testPaper['type'])->updateTestPaper($id, $testPaper, $field);  
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
        $itemCount = $field['itemCount'];
        $itemScore = $field['itemScore'];

        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        $conditions['target']['course'] = array($courseId);
        if (!empty($lessons)){
            $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');;
        }

        //查询各类型设置的数量的题目,
        $items = array();
        $field = array();
        $questions = array();

        foreach ($itemCount as $key => $count) {
            if($count == 0){
                continue;
            }
            $conditions['questionType'] = $key;
            $conditions['parentId'] = 0;
            $questions = $this->getQuestionService()->searchQuestion($conditions, array('createdTime' ,'DESC'), 0, $count);

            //循环题目(question),取出对应的item数据
            $seq = 1;
            foreach ($questions as $question) {
                $field['testId'] = $testId;
                $field['seq'] = $seq;
                $field['questionId'] = $question['id'];
                $field['questionType'] = '\''.$question['questionType'].'\'';
                $field['parentId'] = $question['parentId'];
                $field['score'] = $itemScore[$question['questionType']]==0?$question['score']:$itemScore[$question['questionType']];
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
                    $field['score'] = $itemScore[$question['questionType']]==0?$question['score']:$itemScore[$question['questionType']];
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

    public function findTestPapersByCourseIds(array $id){
        return $this->getQuizPaperCategoryDao() -> findCategorysByCourseIds($id);
    }

    public function findItemsByTestPaperId($testPaperId){
        return $this->getTestItemDao()->findItemsByTestPaperId($testPaperId);
    }

    public function findItemsByTestPaperIdAndQuestionType($testPaperId, $type){
        if(count($type) != 2){
            throw $this->createServiceException('type参数不正确');
        }
        return $this->getTestItemDao()->findItemsByTestPaperIdAndQuestionType($testPaperId, $type);
    }

    private function filterTestPaperFields($testPaper)
    {
        if(!ArrayToolkit::requireds($testPaper, array('name', 'itemCount', 'itemScore', 'target'))){
        	throw $this->createServiceException('缺少必要字段！');
        }

        $diff = array_diff(array_keys($testPaper['itemCount']), array_keys($testPaper['itemScore']));
        if (!empty($diff)) {
            throw $this->createServiceException('itemCount itemScore参数不正确');
        }

        list($targetType, $targetId) = explode('-', $testPaper['target']);

		if(empty($targetId)){
			throw $this->createNotFoundException('target 参数不正确');
		}
		if (!in_array($targetType, array('course','subject','unit','lesson'))) {
            throw $this->createServiceException("target 参数不正确");
        }

        $field = array();

        $field['name'] = $testPaper['name'];
        $field['targetId'] = $targetId;
        $field['targetType'] = $targetType;
		$field['description']   = empty($testPaper['description'])? '' :$testPaper['description'];
		$field['limitedTime']   = empty($testPaper['limitedTime'])? 0 :$testPaper['limitedTime'];;
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
