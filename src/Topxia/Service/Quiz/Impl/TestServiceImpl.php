<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\TestService;
use Topxia\Common\ArrayToolkit;

class TestServiceImpl extends BaseService implements TestService
{
	public function getTestPaper($id)
    {
        return TestPaperSerialize::unserialize($this->getTestPaperDao()->getTestPaper($id));
    }

    public function getTestPaperResult($id)
    {
        return $this->getTestPaperResultDao()->getResult($id);
    }

    public function createTestPaper($testPaper)
    {
        $field = $this->filterTestPaperFields($testPaper);
        $field['createdUserId'] = $this->getCurrentUser()->id;
        $field['createdTime']   = time();
        return TestPaperSerialize::unserialize($this->getTestPaperDao()->addTestPaper(TestPaperSerialize::serialize($field)));
    }

    public function createUpdateTestPaper($id, $testPaper)
    {
        $field = $this->filterTestPaperFields($testPaper);
        return TestPaperSerialize::unserialize($this->getTestPaperDao()->updateTestPaper($id, TestPaperSerialize::serialize($field)));  
    } 

    public function updateTestPaper($id, $testPaper)
    {
        $field['updatedUserId'] = $this->getCurrentUser()->id;
        $field['updatedTime'] = time();
        $field['name']   = empty($testPaper['name'])?"":$testPaper['name'];
        $field['description'] = empty($testPaper['description'])?"":$testPaper['description'];
        $field['limitedTime'] = (int) $testPaper['limitedTime'];
        return $this->getTestPaperDao()->updateTestPaper($id, $field);  
    }

    public function deleteTestPaper($id)
    {
        $testPaper = $this->getTestPaperDao()->getTestPaper($id);
        if (empty($testPaper)) {
            throw $this->createNotFoundException();
        }

        $this->getTestItemDao()->deleteItemsByTestPaperId($id);

        $this->getTestPaperDao()->deleteTestPaper($id);
    }

    public function findTestPapersByTarget($targetType, $targetId, $start, $limit)
    {
        return TestPaperSerialize::unserializes(
            $this->getTestPaperDao()->findTestPapersByTarget($targetType, $targetId, $start, $limit)
        );
    }

    public function findAllTestPapersByTarget ($targetType, $targetId)
    {
        return TestPaperSerialize::unserializes(
            $this->getTestPaperDao()->findTestPaperByTargetIdsAndTargetType(array($targetId), $targetType)
        );
    }

    public function searchTestPaper(array $conditions, array $orderBy, $start, $limit){
        return TestPaperSerialize::unserializes($this->getTestPaperDao()->searchTestPaper($conditions, $orderBy, $start, $limit));
    }

    public function searchTestPaperCount(array $conditions){
        return $this->getTestPaperDao()->searchTestPaperCount($conditions);
    }

    public function getTestItem($id)
    {
        return $this->getTestItemDao()->getItem($id);
    }

    public function buildTestPaper($builder, $options, $testPaperId)
    {
        if(empty($builder)) {
            throw $this->createServiceException('No builder Exists!');
        }

        $testPaper = $this->getTestPaper($testPaperId);

        $builder->prepare($testPaper,$options);   

        $builder->build();       

        return $builder->getQuestions();
    }

    public function buildCheckTestPaper($builder, $options)
    {
        if(empty($builder)) {
            throw $this->createServiceException('No builder Exists!');
        }

        $builder->prepare(array(), $options);

        $builder->validate();

        return  $builder->getMessage();
    }

    public function createItem($testId, $questionId)
    {
    	$question = $this->getQuestionService()->getQuestion($questionId);
    	if(empty($question)){
    		return array();
    	}

    	$field = array();
        $field['testId']       = $testId;
        $field['questionId']   = $question['id'];
        $field['questionType'] = $question['questionType'];
        $field['parentId']     = $question['parentId'];
        $field['score']        = $question['score'];

        $item = $this->getTestItemDao()->addItem($field);

        $this->sortTestItemsByTestId($testId);
        
        return $this->getTestItem($item['id']);
    }

    public function createItems($testId, $field)
    {
        if(!ArrayToolkit::requireds($field, array('ids', 'scores'))){
            throw $this->createServiceException('缺少必要字段！ids score');
        }

        $ids    = $field['ids'];
        $scores = $field['scores'];
        
        $diff = array_diff($ids, $scores);

        if(empty($diff)){
            throw $this->createServiceException('ids, scores diff 参数不正确');
        }

        $count = 0 ;
        $score = 0 ;

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

            if($question['questionType'] != 'material'){
                $count ++;
                $score += $field['score'];
            }
        }


        $this->getTestPaperDao()->updateTestPaper($testId, array('itemCount'=>$count, 'score'=>$score)); 

        $this->sortTestItemsByTestId($testId);
    }

    public function updateItems($testId, $field)
    {
        if(!ArrayToolkit::requireds($field, array('ids', 'scores'))){
            throw $this->createServiceException('缺少必要字段！ids score');
        }

        $ids    = $field['ids'];
        $scores = $field['scores'];

        $ids = array_flip($ids);

        if(count($ids) != count($scores)){
            throw $this->createServiceException('ids scores count参数不正确');
        }

        $items = ArrayToolkit::index($this->findItemsByTestPaperId($testId),'questionId');

        $deleteItems = array_diff_key($items, $ids);
        foreach ($deleteItems as $item) {
            $this->deleteItem($item['id']);
        }

        $addIds = array_flip(array_diff_key($ids, $items));

        $count = 0;
        $score = 0;
        foreach ($ids as $k => $id) {
            $question = $this->getQuestionService()->getQuestion($k);

            if(empty($question)){
                throw $this->createServiceException();
            }

            $field = array();
            $field['testId'] = $testId;
            $field['questionId'] = $question['id'];
            $field['questionType'] = $question['questionType'];
            $field['parentId'] = $question['parentId'];
            $field['score'] = (int) $scores[$id];

            if(in_array($k, $addIds)){
                $item = $this->getTestItemDao()->addItem($field);
            }else{
                $item = $this->getTestItemDao()->updateItem($items[$k]['id'], $field);
            }

            if($question['questionType'] != 'material'){
                $count ++;
                $score += $field['score'];
            }

        }

        $this->getTestPaperDao()->updateTestPaper($testId, array('itemCount'=>$count, 'score'=>$score));

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

    public function deleteItemsByTestPaperId($id)
    {
        $this->getTestItemDao()->deleteItemsByTestPaperId($id);
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

        $seqType =  explode(',', $testPaper['metas']['question_type_seq']);
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

    public function findItemsByTestPaperId($testPaperId)
    {
        return $this->getTestItemDao()->findItemsByTestPaperId($testPaperId);
    }

    public function showTest ($id)
    {
        $userId = $this->getCurrentUser()->id;

        $testResult = $this->getTestPaperResultDao()->getResult($id);
        //如果试卷属于暂停状态，则改成doing继续做
        if ($testResult['status'] == 'paused') {
            $testResult = $this->getTestPaperResultDao()->updateResult($id, array('status' => 'doing'));
        }
        if (in_array($testResult['status'], array('reviewing', 'finished'))) {
            throw $this->createServiceException('已交卷，无法继续考试!');
        }

        $items = $this->findItemsByTestPaperId($testResult['testId']);
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

    public function findQuestionsByTestId ($testId)
    {
        $items = $this->getTestItemDao()->findItemsByTestPaperId($testId);

        $materialIds = $this->findMaterial($items);
        $materialQuestions = $this->getQuestionService()->findQuestionsByParentIds($materialIds);

        $questionIds = ArrayToolkit::column($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $questions = array_merge($questions, $materialQuestions);

        $questions = QuestionSerialize::unserializes($questions);

        $questions = ArrayToolkit::index($questions, 'id');


        $items = ArrayToolkit::index($items, 'questionId');

        $choices = $this->getQuestionService()->findChoicesByQuestionIds(array_keys($questions));

        $questions = $this->makeTest($questions, $choices);

        foreach ($questions as $key => $question) {
            $questions[$key]['itemScore'] = $items[$key]['score'];
            $questions[$key]['seq'] = $items[$key]['seq'];
        }

        // $questions = $this->makeMaterial($questions);

        return $questions;
    }

    public function testResults($id)
    {
        // if ($userId == null) {
        //     $userId = $this->getCurrentUser()->id;
        // }
        $answers = $this->getDoTestDao()->findTestResultsByTestPaperResultId($id);
        $answers = QuestionSerialize::unserializes($answers);
        $answers = ArrayToolkit::index($answers, 'questionId');

        $testResult = $this->getTestPaperResultDao()->getResult($id);

        $items = $this->findItemsByTestPaperId($testResult['testId']);

        $materialIds = $this->findMaterial($items);
        $materialQuestions = $this->getQuestionService()->findQuestionsByParentIds($materialIds);

        $questionIds = ArrayToolkit::column($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $questions = array_merge($questions, $materialQuestions);

        $questions = QuestionSerialize::unserializes($questions);

        $questions = ArrayToolkit::index($questions, 'id');


        $items = ArrayToolkit::index($items, 'questionId');

        foreach ($answers as $key => $answer) {
            //可能会查不到题目的问题，例如题目被删除，需要提示
            $questions[$key]['itemScore'] = $items[$key]['score'];

            $questions[$key]['testResult'] = $answer;

            if ($questions[$key]['questionType'] == 'fill') {
                var_dump($questions[$key]);exit();
                $questions[$key]['answer'] = array_map(function($answer){
                    return str_replace('|', '或者', $answer);
                }, $questions[$key]['answer']);
            }
        }

        foreach ($questions as $key => $question) {
            $questions[$key]['itemScore'] = $items[$key]['score'];
            $questions[$key]['seq'] = $items[$key]['seq'];
        }

        $choices = $this->getQuestionService()->findChoicesByQuestionIds(array_keys($questions));

        $questions = $this->makeTest($questions, $choices);

        // $questions = $this->makeMaterial($questions);

        return $questions;
    }

    public function makeFinishTestResults ($id)
    {
        $userId = $this->getCurrentUser()->id;
        if (empty($userId)){
            throw $this->createServiceException("当前用户不存在!");        
        }

        $testResult = $this->getTestPaperResultDao()->getResult($id);

        if ($testResult['userId'] != $userId) {
            throw $this->createAccessDeniedException('无权修改其他学员的试卷！');
        }

        if (in_array($testResult['status'], array('reviewing', 'finished'))) {
            throw $this->createServiceException("已经交卷的试卷不能更改答案!");
        }


        //得到当前用户答案
        $answers = $this->getDoTestDao()->findTestResultsByTestPaperResultId($testResult['id']);
        $answers = QuestionSerialize::unserializes($answers);
        $answers = ArrayToolkit::index($answers, 'questionId');
        
        $items = $this->findItemsByTestPaperId($testResult['testId']);
        //材料题子题目
        $materialIds = $this->findMaterial($items);
        $materialQuestions = $this->getQuestionService()->findQuestionsByParentIds($materialIds);
        //非材料题子题目的题目id
        $questionIds = ArrayToolkit::column($items, 'questionId');
        //题目
        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);
        $questions = array_merge($questions, $materialQuestions);

        $questions = QuestionSerialize::unserializes($questions);

        $questions = ArrayToolkit::index($questions, 'id');

        foreach ($items as $key => $value) {
            $questions[$value['questionId']]['score'] = $value['score'];
        }

        $results = $this->makeTestResults($answers, $questions);

        $results['oldAnswers'] = array_map(function($result){
            $result['answer'] = json_encode($result['answer']);
            return $result;
        }, $results['oldAnswers']);
        $results['newAnswers'] = array_map(function($result){
            $result['answer'] = json_encode($result['answer']);
            return $result;
        }, $results['newAnswers']);

        //记分
        $this->getDoTestDao()->updateItemResults($results['oldAnswers'], $testResult['id']);
        //未答题目记分
        $this->getDoTestDao()->addItemResults($results['newAnswers'], $testResult['id'], $userId);

        return $this->getDoTestDao()->findTestResultsByTestPaperResultId($testResult['id']);
    }

    private function makeTestResults ($answers, $questions)
    {

        $materials = $this->findMaterial($questions);
        // $results = array();
        $newAnswers = array();
        $oldAnswers = array();
        foreach ($questions as $key => $question) {
            if ($question['questionType'] == 'material'){
                continue;
            }

            if (empty($answers[$key])) {
                $newAnswers[] = array(
                    'questionId' => $key,
                    'status' => 'noAnswer',
                    'score' => 0,
                    'answer' => ''
                );

                // $results[$key] = $question;
                continue;
            }

            if (!in_array($question['questionType'], array('single_choice', 'choice', 'determine', 'fill', 'material'))){
                continue;
            }

            if ($question['questionType'] == 'single_choice' or $question['questionType'] == 'choice') {

                $diff = array_diff($question['answer'], $answers[$key]['answer']);

                if (count($question['answer']) == count($answers[$key]['answer']) && empty($diff)) {
                    $answers[$key]['status'] = 'right';
                    $answers[$key]['score'] = $question['score'];

                    // $question['result'] = 'right';
                } else {
                    $answers[$key]['status'] = 'wrong';
                    $answers[$key]['score'] = 0;

                    // $question['result'] = 'wrong';
                }
            }

            if ($question['questionType'] == 'determine') {
                $diff = array_diff($question['answer'], $answers[$key]['answer']);

                if (count($question['answer']) == count($answers[$key]['answer']) && empty($diff)) {
                    $answers[$key]['status'] = 'right';
                    $answers[$key]['score'] = $question['score'];
                    // $question['result'] = 'right';
                } else {
                    $answers[$key]['status'] = 'wrong';
                    $answers[$key]['score'] = 0;
                    // $question['result'] = 'wrong';
                }
            }

            if ($question['questionType'] == 'fill') {
                $right = 0;
                $noAnswerCount = 0;
                foreach ($question['answer'] as $k => $value) {
                    $value = explode('|', $value);
                    if (count($value) == 1) {
                        if (trim($answers[$key]['answer'][$k]) == ''){
                            $noAnswerCount++;
                        }
                        if ($value[0] == trim($answers[$key]['answer'][$k])) {
                            $right++;
                        }
                    } else {
                        foreach ($value as $v) {
                            if (trim($answers[$key]['answer'][$k]) == ''){
                                $noAnswerCount++;
                            }
                            if ($v == trim($answers[$key]['answer'][$k])) {
                                $right++;
                            }
                        }
                    }
                }

                if ($noAnswerCount == count($question['answer'])) {
                    $answers[$key]['status'] = 'noAnswer';
                } elseif ($right == count($question['answer'])) {
                    $answers[$key]['status'] = 'right';
                } else {
                    $answers[$key]['status'] = 'wrong';
                }

                $answers[$key]['score'] = round($question['score'] * $right / count($question['answer']), 1);

                // $question['result'] = $right;
            }

            $oldAnswers[$key] = $answers[$key];
        }

        $oldAnswers = array_map(function($oldAnswer){
            return ArrayToolkit::parts($oldAnswer, array('questionId', 'status', 'score', 'answer'));
        }, $oldAnswers);

        return array(
            'oldAnswers' => $oldAnswers,
            'newAnswers' => ArrayToolkit::index($newAnswers, 'questionId')
        );
    }

    

    public function makeTest ($questions, $answers)
    {
        foreach ($answers as $key => $value) {
            if (!array_key_exists('choices', $questions[$value['questionId']])) {
                $questions[$value['questionId']]['choices'] = array();
            }
            // array_push($questions[$value['questionId']]['choices'], $value);
            $questions[$value['questionId']]['choices'][$value['id']] = $value;
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

    public function submitTest ($answers, $id)
    {
        if (empty($answers)) {
            return array();
        }
        //是否需要校验test_paper_result表中的userId跟当前用户id是否一致
        $user = $this->getCurrentUser();

        $testResult = $this->getTestPaperResultDao()->getResult($id);

        if ($testResult['userId'] != $user['id']) {
            throw $this->createAccessDeniedException('无权修改其他学员的试卷！');
        }

        if (in_array($testResult['status'], array('reviewing', 'finished'))) {
            throw $this->createServiceException("已经交卷的试卷不能更改答案!");
        }


        $answers = array_map(function($answer){
            return json_encode($answer);
        }, $answers);

        //过滤待补充
        
        //已经有记录的
        $itemResults = $this->filterTestAnswers($answers, $testResult['id']);
        $itemIdsOld = ArrayToolkit::index($itemResults, 'questionId');

        $answersOld = ArrayToolkit::parts($answers, array_keys($itemIdsOld));

        if (!empty($answersOld)) {
            $this->getDoTestDao()->updateItemAnswers($answersOld, $testResult['id']);
        }
        //还没记录的
        $itemIdsNew = array_diff(array_keys($answers), array_keys($itemIdsOld));

        $answersNew = ArrayToolkit::parts($answers, $itemIdsNew);

        if (!empty($answersNew)) {
            $this->getDoTestDao()->addItemAnswers($answersNew, $testResult['testId'], $testResult['id'], $user['id']);
        }

        //测试数据
        return $this->filterTestAnswers($answers, $testResult['id']);

    }

    public function makeTeacherFinishTest ($id, $teacherId, $field)
    {
        $testResults = array();
        
        foreach ($field as $key => $value) {
            $keys = explode('_', $key);

            if (!is_numeric($keys[1])) {
                throw $this->createServiceException('得分必须为数字！');
            }

            $testResults[$keys[1]][$keys[0]] = $value;
        }
        //是否要加入教师阅卷的锁
        $this->getDoTestDao()->updateItemEssays($testResults, $id);

        $paperResult = $this->getTestPaperResultDao()->getResult($id);

        $subjectiveScore = array_sum(ArrayToolkit::column($testResults, 'score'));

        $totalScore = $subjectiveScore + $paperResult['objectiveScore'];

        return $this->getTestPaperResultDao()->updateResult($id, array(
            'score' => $totalScore,
            'subjectiveScore' => $subjectiveScore,
            'status' => 'finished',
            'checkTeacherId' => $teacherId,
            'checkedTime' => time()
        ));
    }

    private function filterTestAnswers ($answers, $testPaperResultId)
    {
        return $this->getDoTestDao()->findTestResultsByItemIdAndTestId(array_keys($answers), $testPaperResultId);
    }

    public function startTest ($testId, $userId, $testPaper)
    {

        $testPaperResult = array(
            'paperName' => $testPaper['name'],
            'testId' => $testId,
            'userId' => $userId,
            'limitedTime' => $testPaper['limitedTime'],
            'beginTime' => time(),
            'status' => 'doing',
            'remainTime' => $testPaper['limitedTime'] * 60
        );

        return $this->getTestPaperResultDao()->addResult($testPaperResult);
    }

    public function finishTest ($id, $userId, $remainTime)
    {
        $testResults = $this->getDoTestDao()->findTestResultsByTestPaperResultId($id);

        $fields['status'] = $this->isExistsEssay($testResults) ? 'reviewing' : 'finished';

        $fields['objectiveScore'] = $this->sumScore($testResults);

        if (!$this->isExistsEssay($testResults)){
            $fields['score'] = $fields['objectiveScore'];
        }

        $fields['rightItemCount'] = $this->getDoTestDao()->findRightItemCountByTestPaperResultId($id);

        $fields['remainTime'] = $remainTime;
        $fields['endTime'] = time();

        return $this->getTestPaperResultDao()->updateResult($id, $fields);
    }

    public function updatePaperResult ($id, $status, $remainTime)
    {
        $testResults = $this->getDoTestDao()->findTestResultsByTestPaperResultId($id);

        $fields['status'] = $status;

        $fields['remainTime'] = $remainTime;

        return $this->getTestPaperResultDao()->updateResult($id, $fields);
    }

    private function isExistsEssay ($testResults)
    {
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($testResults, 'questionId'));
        foreach ($questions as $value) {
            if ($value['questionType'] == 'essay') {
                return true;
            }
        }
        return false;
    }

    private function sumScore ($testResults)
    {
        $score = 0;
        foreach ($testResults as $value) {
            $score += $value['score'];
        }
        return $score;
    }

    public function canTeacherCheck($id)
    {
        $paper = $this->getTestPaperDao()->getTestPaper($id);
        if (!$paper) {
            throw $this->createServiceException('试卷不存在');
        }

        $userId = $this->getCurrentUser()->id;

        if ($paper['targetType'] == 'course') {
            $course = $this->getCourseService()->getCourse($paper['targetId']);

            if (in_array($userId, $course['teacherIds'])) {
                return $userId;
            }
        }
        return false;
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

        $metas = array( 'question_type_seq' => 
                implode(',', array_keys($testPaper['itemCounts'])));

        $field = array();

        $field['name']          = $testPaper['name'];
        $field['targetId']      = $target['1'];
        $field['targetType']    = $target['0'];
        $field['pattern']       = 'QuestionType';
        $field['metas']         = $metas;
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

    private function getTestPaperResultDao()
    {
        return $this->createDao('Quiz.TestPaperResultDao');
    }

}


class TestPaperSerialize
{
    public static function serialize(array $item)
    {
        if (isset($item['metas'])) {
            $item['metas'] = json_encode($item['metas']);
        }

        return $item;
    }

    public static function unserialize(array $item = null)
    {
        if (empty($item)) {
            return null;
        }

        $item['metas'] = json_decode($item['metas'], true);
        foreach ($item['metas'] as $key => $value) {
            $item['metas'][$key] = explode(',', $value);
        }
        return $item;
    }

    public static function unserializes(array $items)
    {
        return array_map(function($item) {
            return TestPaperSerialize::unserialize($item);
        }, $items);
    }
}


