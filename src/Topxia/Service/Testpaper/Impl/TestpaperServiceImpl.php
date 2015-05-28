<?php
namespace Topxia\Service\Testpaper\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Testpaper\TestpaperService;
use Topxia\Service\Testpaper\Builder\TestpaperBuilderFactory;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Question\Type\QuestionTypeFactory;

class TestpaperServiceImpl extends BaseService implements TestpaperService
{
	public function getTestpaper($id)
	{
		return $this->getTestpaperDao()->getTestpaper($id);
	}

    public function getTestpaperResult($id)
    {
        return $this->getTestpaperResultDao()->getTestpaperResult($id);
    }

    public function findTestpapersByIds($ids)
    {
        $testpapers = $this->getTestpaperDao()->findTestpapersByIds($ids);
        return ArrayToolkit::index($testpapers, 'id');
    }

	public function searchTestpapers($conditions, $sort, $start, $limit)
	{
        return $this->getTestpaperDao()->searchTestpapers($conditions, $sort, $start, $limit);
    }

    public function searchTestpapersCount($conditions)
    {
        return $this->getTestpaperDao()->searchTestpapersCount($conditions);
	}

    public function searchTestpaperResultsCount($conditions)
    {
        return $this->getTestpaperResultDao()->searchTestpaperResultsCount($conditions);
    }

    public function searchTestpapersScore($conditions)
    {
        return $this->getTestpaperResultDao()->searchTestpapersScore($conditions);
    }

    public function createTestpaper($fields)
    {
        $testpaper = $this->getTestpaperDao()->addTestpaper($this->filterTestpaperFields($fields, 'create'));
        $items = $this->buildTestpaper($testpaper['id'], $fields);

        return array($testpaper, $items);
    }

    public function updateTestpaper($id, $fields)
    {
        $testpaper = $this->getTestpaperDao()->getTestpaper($id);
        if (empty($testpaper)) {
            throw $this->createServiceException("Testpaper #{$id} is not found, update testpaper failure.");
        }
        $fields = $this->filterTestpaperFields($fields, 'update');
        return $this->getTestpaperDao()->updateTestpaper($id, $fields);
    }

    private function filterTestpaperFields($fields, $mode = 'create')
    {
        $filtedFields = array();
        $filtedFields['updatedUserId'] = $this->getCurrentUser()->id;
        $filtedFields['updatedTime']   = time();
        if ($mode == 'create') {
            if(!ArrayToolkit::requireds($fields, array('name', 'pattern', 'target'))){
                throw $this->createServiceException('缺少必要字段！');
            }
            $filtedFields['name'] = $fields['name'];
            $filtedFields['target'] = $fields['target'];
            $filtedFields['pattern'] = $fields['pattern'];
            $filtedFields['description']   = empty($fields['description'])? '' : $fields['description'];
            $filtedFields['limitedTime']   = empty($fields['limitedTime']) ? 0 : (int) $fields['limitedTime'];
            $filtedFields['metas']   = empty($fields['metas'])? array() : $fields['metas'];
            $filtedFields['status'] = 'draft';
            $filtedFields['createdUserId'] = $this->getCurrentUser()->id;
            $filtedFields['createdTime']   = time();
        } else {
            if (array_key_exists('name', $fields)) {
                $filtedFields['name'] = empty($fields['name']) ? '' : $fields['name'];
            }

            if (array_key_exists('description', $fields)) {
                $filtedFields['description'] = empty($fields['description']) ? '' : $fields['description'];
            }

            if (array_key_exists('limitedTime', $fields)) {
                $filtedFields['limitedTime'] = empty($fields['limitedTime']) ? 0 : (int) $fields['limitedTime'];
            }

            if (array_key_exists('passedScore', $fields)) {
                $filtedFields['passedScore'] = empty($fields['passedScore']) ? 0 : (float) $fields['passedScore'] ;
            }
        }

        return $filtedFields;
    }



    public function publishTestpaper($id)
    {
        $testpaper = $this->getTestpaperDao()->getTestpaper($id);
        if (empty($testpaper)){
            throw $this->createNotFoundException();
        }
        if (!in_array($testpaper['status'], array('closed', 'draft'))){
            throw $this->createServiceException('试卷状态不合法!');
        }
        $testpaper = array(
            'status' => 'open'
        );
        return $this->getTestpaperDao()->updateTestpaper($id, $testpaper);
    }

    public function closeTestpaper($id)
    {
        $testpaper = $this->getTestpaperDao()->getTestpaper($id);
        if (empty($testpaper)){
            throw $this->createNotFoundException();
        }
        if (!in_array($testpaper['status'], array('open'))){
            throw $this->createAccessDeniedException('试卷状态不合法!');
        }
        $testpaper = array(
            'status' => 'closed'
        );
        return $this->getTestpaperDao()->updateTestpaper($id, $testpaper);
    }

    public function deleteTestpaper($id)
    {
        $this->getTestpaperDao()->deleteTestpaper($id);
        $this->getTestpaperItemDao()->deleteItemsByTestpaperId($id);
    }

    public function deleteTestpaperByIds(array $ids)
    {

    }

    public function buildTestpaper($id, $options)
    {
        $testpaper = $this->getTestpaperDao()->getTestpaper($id);
        if (empty($testpaper)) {
            throw $this->createServiceException("Testpaper #{$id} is not found.");
        }

        $this->getTestpaperItemDao()->deleteItemsByTestpaperId($testpaper['id']);


        $builder = TestpaperBuilderFactory::create($testpaper['pattern']);

        $result = $builder->build($testpaper, $options);
        if ($result['status'] != 'ok') {
            throw $this->createServiceException("Build testpaper #{$id} items error.");
        }

        $items = array();
        $types = array();

        $totalScore = 0;
        $seq = 1;
        foreach ($result['items'] as $item) {
            $questionType = QuestionTypeFactory::create($item['questionType']);

            $item['seq'] = $seq;
            if (!$questionType->canHaveSubQuestion()) {
                $seq++;
                $totalScore += $item['score'] ;
            }

            $items[] = $this->getTestpaperItemDao()->addItem($item);
            if ($item['parentId'] == 0 && !in_array($item['questionType'], $types)) {
                $types[] = $item['questionType'];
            }
        }

        $metas = empty($testpaper['metas']) ? array() : $testpaper['metas'];
        $metas['question_type_seq'] = $types;
        $metas['missScore'] = $options['missScores'];

        $this->getTestpaperDao()->updateTestpaper($testpaper['id'], array(
            'itemCount' => $seq -1,
            'score' => $totalScore,
            'metas' => $metas,
        ));

        return $items;
    }

    public function canBuildTestpaper($builder, $options)
    {
        $builder = TestpaperBuilderFactory::create($builder);
        return $builder->canBuild($options);
    }

    public function findTestpaperResultsByUserId ($id, $start, $limit)
    {
        return $this->getTestpaperResultDao()->findTestpaperResultsByUserId($id, $start, $limit);
    }

    public function findTestpaperResultsCountByUserId ($id)
    {
        return $this->getTestpaperResultDao()->findTestpaperResultsCountByUserId($id);
    }

    public function findTestpaperResultByTestpaperIdAndUserIdAndActive($testpaperId, $userId)
    {
    	return $this->getTestpaperResultDao()->findTestpaperResultByTestpaperIdAndUserIdAndActive($testpaperId, $userId);
    }

    public function findTestpaperResultsByTestIdAndStatusAndUserId($testpaperId, $userId, array $status)
    {
    	return $this->getTestpaperResultDao()->findTestpaperResultsByTestIdAndStatusAndUserId($testpaperId, $status, $userId);
    }

    public function findTestpaperResultsByStatusAndTestIds ($ids, $status, $start, $limit)
    {
        return $this->getTestpaperResultDao()->findTestpaperResultsByStatusAndTestIds($ids, $status, $start, $limit);
    }

    public function findTestpaperResultCountByStatusAndTestIds ($ids, $status)
    {
        return $this->getTestpaperResultDao()->findTestpaperResultCountByStatusAndTestIds($ids, $status);
    }

    public function findTestpaperResultsByStatusAndTeacherIds ($ids, $status, $start, $limit)
    {
        return $this->getTestpaperResultDao()->findTestpaperResultsByStatusAndTeacherIds($ids, $status, $start, $limit);
    }

    public function findTestpaperResultCountByStatusAndTeacherIds ($ids, $status)
    {
        return $this->getTestpaperResultDao()->findTestpaperResultCountByStatusAndTeacherIds($ids, $status);
    }

    public function findAllTestpapersByTarget ($id)
    {
        $target = 'course-'.$id;
        return $this->getTestpaperDao()->findTestpaperByTargets(array($target));
    }

    public function findAllTestpapersByTargets(array $ids)
    {
        $targets=array();
        foreach ($ids as $id) {
            $targets[]='course-'.$id;
        }
        return $this->getTestpaperDao()->findTestpaperByTargets($targets);
    }



    public function startTestpaper($id, $target)
    {
    	$testpaper = $this->getTestpaperDao()->getTestpaper($id);

    	$testpaperResult = array(
            'paperName' => $testpaper['name'],
            'testId' => $id,
            'userId' => $this->getCurrentUser()->id,
            'limitedTime' => $testpaper['limitedTime'],
            'beginTime' => time(),
            'status' => 'doing',
            'usedTime' => 0,
            'target' => empty($target['type']) ? '' : $testpaper['target']."/".$target['type']."-".$target['id']
        );

        return $this->getTestpaperResultDao()->addTestpaperResult($testpaperResult);
    }

    private function completeQuestion($items, $questions)
    {
        foreach ($items as $item) {
            if (!in_array($item['questionId'], ArrayToolkit::column($questions, 'id'))){
                $questions[$item['questionId']] = array(
                    'isDeleted' => true,
                    'stem' => '此题已删除',
                    'score' => 0,
                    'answer' => ''
                );
            }
        }
        return $questions;
    }

    public function previewTestpaper($testpaperId)
    {
        $items = $this->getTestpaperItems($testpaperId);
        $items = ArrayToolkit::index($items, 'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));
        $questions = ArrayToolkit::index($questions, 'id');

        $questions = $this->completeQuestion($items, $questions);

        $formatItems = array();
        foreach ($items as $questionId => $item) {
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

        ksort($formatItems);
        return $formatItems;
    }

    public function showTestpaper($testpaperResultId, $isAccuracy = null)
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

    private function makeAccuracy ($items)
    {
        $accuracyResult = array(
            'right' => 0,
            'partRight' => 0,
            'wrong' => 0,
            'noAnswer' => 0,
            'all' => 0,
            'score' => 0,
            'totalScore' => 0
        );
        $accuracy = array(
            'single_choice' => $accuracyResult,
            'choice' => $accuracyResult,
            'uncertain_choice' => $accuracyResult,
            'determine' => $accuracyResult,
            'fill' => $accuracyResult,
            'essay' => $accuracyResult,
            'material' => $accuracyResult
        );

        foreach ($items as $item) {

            if ($item['questionType'] == 'material'){
                if (!array_key_exists('items', $item)){
                    continue;
                }
                foreach ($item['items'] as $key => $v) {

                    if ($v['questionType'] == 'essay'){
                        $accuracy['material']['hasEssay'] = true;
                    }

                    $accuracy['material']['score'] += $v['question']['testResult']['score'];
                    $accuracy['material']['totalScore'] += $v['score'];

                    $accuracy['material']['all']++;
                    if ($v['question']['testResult']['status'] == 'right'){
                        $accuracy['material']['right']++;
                    }
                    if ($v['question']['testResult']['status'] == 'partRight'){
                        $accuracy['material']['partRight']++;
                    }
                    if ($v['question']['testResult']['status'] == 'wrong'){
                        $accuracy['material']['wrong']++;
                    }
                    if ($v['question']['testResult']['status'] == 'noAnswer'){
                        $accuracy['material']['noAnswer']++;
                    }
                }
            } else {

                $accuracy[$item['questionType']]['score'] += $item['question']['testResult']['score'];
                $accuracy[$item['questionType']]['totalScore'] += $item['score'];

                $accuracy[$item['questionType']]['all']++;
                if ($item['question']['testResult']['status'] == 'right'){
                    $accuracy[$item['questionType']]['right']++;
                }
                if ($item['question']['testResult']['status'] == 'partRight'){
                    $accuracy[$item['questionType']]['partRight']++;
                }
                if ($item['question']['testResult']['status'] == 'wrong'){
                    $accuracy[$item['questionType']]['wrong']++;
                }
                if ($item['question']['testResult']['status'] == 'noAnswer'){
                    $accuracy[$item['questionType']]['noAnswer']++;
                }

            }
        }

        return $accuracy;
    }

    public function makeTestpaperResultFinish ($id)
    {
        $userId = $this->getCurrentUser()->id;
        if (empty($userId)){
            throw $this->createServiceException("当前用户不存在!");        
        }

        $testpaperResult = $this->getTestpaperResultDao()->getTestpaperResult($id);

        if ($testpaperResult['userId'] != $userId) {
            throw $this->createAccessDeniedException('无权修改其他学员的试卷！');
        }

        if (in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
            throw $this->createServiceException("已经交卷的试卷不能更改答案!");
        }

        $items = $this->getTestpaperItems($testpaperResult['testId']);
        $items = ArrayToolkit::index($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));

        //得到当前用户答案
        $answers = $this->getTestpaperItemResultDao()->findTestResultsByTestpaperResultId($testpaperResult['id']);
        $answers = ArrayToolkit::index($answers, 'questionId');

        $answers = $this->formatAnswers($answers, $items);

        $answers = $this->getQuestionService()->judgeQuestions($answers, true);

        $answers = $this->makeScores($answers, $items);

        $questions = $this->completeQuestion($items, $questions);

        foreach ($answers as $questionId => $answer) {
            if($answer['status'] == 'noAnswer'){
                $answer['answer'] = array_pad(array(), count($questions[$questionId]['answer']), '');

                $answer['testId'] = $testpaperResult['testId'];
                $answer['testPaperResultId'] = $testpaperResult['id'];
                $answer['userId'] = $userId;
                $answer['questionId'] = $questionId;
                $this->getTestpaperItemResultDao()->addItemResult($answer);
            }
        }

        //记分
        $this->getTestpaperItemResultDao()->updateItemResults($answers, $testpaperResult['id']);

        return $this->getTestpaperItemResultDao()->findTestResultsByTestpaperResultId($testpaperResult['id']);
    }

    private function formatAnswers($answers, $items)
    {
        $results = array();
        foreach ($items as $item) {
            if (!array_key_exists($item['questionId'], $answers)){
                $results[$item['questionId']] = array();
            } else {
                $results[$item['questionId']] = $answers[$item['questionId']]['answer'];
            }
        }
        return $results;
    }

    public function makeScores($answers, $items)
    {
        foreach ($answers as $questionId => $answer) {
            if ($answer['status'] == 'right') {
                $answers[$questionId]['score'] = $items[$questionId]['score'];
            } elseif ($answer['status'] == 'partRight') {

                if ($items[$questionId]['questionType'] == 'fill') {
                    $answers[$questionId]['score'] = ($items[$questionId]['score'] * $answer['percentage']) /100 ;
                    $answers[$questionId]['score'] = number_format($answers[$questionId]['score'], 1, '.', '');
                } else {
                    $answers[$questionId]['score'] = $items[$questionId]['missScore'];
                }
   
            } else {
                $answers[$questionId]['score'] = 0;
            }
        }
        return $answers;
    }


    public function finishTest($id, $userId, $usedTime)
    {
        $itemResults = $this->getTestpaperItemResultDao()->findTestResultsByTestpaperResultId($id);

        $testpaperResult = $this->getTestpaperResultDao()->getTestpaperResult($id);

        $testpaper = $this->getTestpaperDao()->getTestpaper($testpaperResult['testId']);

        $fields['status'] = $this->isExistsEssay($itemResults) ? 'reviewing' : 'finished';

        $accuracy = $this->sumScore($itemResults);
        $fields['objectiveScore'] = $accuracy['sumScore'];

        if (!$this->isExistsEssay($itemResults)){
            $fields['score'] = $fields['objectiveScore'];
        }

        $fields['rightItemCount'] = $accuracy['rightItemCount'];

        if ($testpaper['passedScore'] > 0) {
            $fields['passedStatus'] = $fields['score'] >= $testpaper['passedScore'] ? 'passed' : 'unpassed';
        } else {
            $fields['passedStatus'] = 'none';
        }

        $fields['usedTime'] = $usedTime + $testpaperResult['usedTime'];
        $fields['endTime'] = time();
        $fields['active'] = 1;
        $fields['checkedTime'] = time();

        $this->getTestpaperResultDao()->updateTestpaperResultActive($testpaperResult['testId'],$testpaperResult['userId']);

        $testpaperResult = $this->getTestpaperResultDao()->updateTestpaperResult($id, $fields);

        $this->dispatchEvent(
            'testpaper.finish', 
            new ServiceEvent($testpaper, array('testpaperResult' => $testpaperResult))
        );

        return $testpaperResult;
    }

    public function isExistsEssay ($itemResults)
    {
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($itemResults, 'questionId'));
        foreach ($questions as $value) {
            if ($value['type'] == 'essay') {
                return true;
            }
        }
        return false;
    }

    private function sumScore ($itemResults)
    {
        $score = 0;
        $rightItemCount = 0;
        foreach ($itemResults as $itemResult) {
            $score += $itemResult['score'];
            if ($itemResult['status'] == 'right'){
                $rightItemCount++;
            }
        }
        return array(
            'sumScore' => $score,
            'rightItemCount' => $rightItemCount
        );
    }

    public function makeTeacherFinishTest ($id, $paperId, $teacherId, $field)
    {
        $testResults = array();
        
        $teacherSay = $field['teacherSay'];
        unset($field['teacherSay']);


        $items = $this->getTestpaperItemDao()->findItemsByTestpaperId($paperId);
        $items = ArrayToolkit::index($items, 'questionId');

        $userAnswers = $this->getTestpaperItemResultDao()->findTestResultsByTestpaperResultId($id);
        $userAnswers = ArrayToolkit::index($userAnswers, 'questionId');

        foreach ($field as $key => $value) {
            $keys = explode('_', $key);

            if (!is_numeric($keys[1])) {
                throw $this->createServiceException('得分必须为数字！');
            }

            $testResults[$keys[1]][$keys[0]] = $value;
            $userAnswer = $userAnswers[$keys[1]]['answer'];
            if ($keys[0] == 'score'){
                if ($value == $items[$keys[1]]['score']){
                    $testResults[$keys[1]]['status'] = 'right';
                } elseif ($userAnswer[0] == '') {
                    $testResults[$keys[1]]['status'] = 'noAnswer';
                } else {
                    $testResults[$keys[1]]['status'] = 'wrong';
                }
            }
        }
        //是否要加入教师阅卷的锁
        $this->getTestpaperItemResultDao()->updateItemEssays($testResults, $id);

        $this->getQuestionService()->statQuestionTimes($testResults);

        $testpaperResult = $this->getTestpaperResultDao()->getTestpaperResult($id);

        $subjectiveScore = array_sum(ArrayToolkit::column($testResults, 'score'));

        $totalScore = $subjectiveScore + $testpaperResult['objectiveScore'];

        return $this->getTestpaperResultDao()->updateTestpaperResult($id, array(
            'score' => $totalScore,
            'subjectiveScore' => $subjectiveScore,
            'status' => 'finished',
            'checkTeacherId' => $teacherId,
            'checkedTime' => time(),
            'teacherSay' => $teacherSay
        ));
    }

    public function finishTestpaper($resultId)
    {

    }

    public function submitTestpaperAnswer($id, $answers)
    {
        if (empty($answers)) {
            return array();
        }

        $user = $this->getCurrentUser();

        $testpaperResult = $this->getTestpaperResultDao()->getTestpaperResult($id);

        if ($testpaperResult['userId'] != $user['id']) {
            throw $this->createAccessDeniedException('无权修改其他学员的试卷！');
        }

        if (in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
            throw $this->createServiceException("已经交卷的试卷不能更改答案!");
        }

        //已经有记录的
        $itemResults = $this->filterTestAnswers($testpaperResult['id'], $answers);
        $itemIdsOld = ArrayToolkit::index($itemResults, 'questionId');

        $answersOld = ArrayToolkit::parts($answers, array_keys($itemIdsOld));

        if (!empty($answersOld)) {
            $this->getTestpaperItemResultDao()->updateItemAnswers($testpaperResult['id'], $answersOld);
        }
        //还没记录的
        $itemIdsNew = array_diff(array_keys($answers), array_keys($itemIdsOld));

        $answersNew = ArrayToolkit::parts($answers, $itemIdsNew);

        if (!empty($answersNew)) {
            $this->getTestpaperItemResultDao()->addItemAnswers($testpaperResult['id'], $answersNew, $testpaperResult['testId'], $user['id']);
        }

        //测试数据
        return $this->filterTestAnswers($testpaperResult['id'], $answers);
    }

    private function filterTestAnswers ($testpaperResultId, $answers)
    {
        return $this->getTestpaperItemResultDao()->findTestResultsByItemIdAndTestId(array_keys($answers), $testpaperResultId);
    }

    public function reviewTestpaper($resultId, $items, $remark = null)
    {

    }

    public function updateTestpaperResult($id, $usedTime)
    {
        $testpaperResult = $this->getTestpaperResultDao()->getTestpaperResult($id);

        $fields['usedTime'] = $usedTime + $testpaperResult['usedTime'];

        $fields['updateTime'] = time();

        $fields['endTime'] = time();
        $fields['active'] = 1;

        $this->getTestpaperResultDao()->updateTestpaperResultActive($testpaperResult['testId'],$testpaperResult['userId']);

        return $this->getTestpaperResultDao()->updateTestpaperResult($id, $fields);
    }

    public function getTestpaperItems($testpaperId)
    {
        return $this->getTestpaperItemDao()->findItemsByTestpaperId($testpaperId);
    }

    public function updateTestpaperItems($testpaperId, $items)
    {

        $testpaper = $this->getTestpaper($testpaperId);
        if (empty($testpaperId)) {
            throw $this->createServiceException();
        }
        $existItems = $this->getTestpaperItems($testpaperId);

        $existItems = ArrayToolkit::index($existItems, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));
        if (count($items) != count($questions)) {
            throw $this->createServiceException('数据缺失');
        }

        $types = array();
        $totalScore = 0;
        $seq = 1;

        $items = ArrayToolkit::index($items, 'questionId');

        foreach ($items as $questionId => $item) {
            if ($questions[$questionId]['type'] == 'material' ) {
                $items[$questionId]['score'] = 0;
            }
        }
        foreach ($items as $questionId => $item) {
            if ($questions[$questionId]['parentId'] >0 ) {
                $items[$questions[$questionId]['parentId']]['score'] += $item['score'];
            }
        }


        foreach ($items as $item) {
            $question = $questions[$item['questionId']];
            $item['seq'] = $seq;
            if ($question['subCount'] == 0) {
                $seq ++;
                $totalScore += $item['score'] ;
            }

            if (empty($existItems[$item['questionId']])) {
                $item['questionType'] = $question['type'];
                $item['parentId'] = $question['parentId'];
                // @todo, wellming.

                if (array_key_exists('missScore', $testpaper['metas']) and array_key_exists($question['type'], $testpaper['metas']['missScore'])) {
                    $item['missScore'] = $testpaper['metas']['missScore'][$question['type']];
                } else {
                    $item['missScore'] = 0;
                }

                $item['testId'] = $testpaperId;
                $item = $this->getTestpaperItemDao()->addItem($item);
            } else {

                $existItem = $existItems[$item['questionId']];

                if ($item['seq'] != $existItem['seq'] or $item['score'] != $existItem['score']) {
                    $existItem['seq'] = $item['seq'];
                    $existItem['score'] = $item['score'];
                    $item = $this->getTestpaperItemDao()->updateItem($existItem['id'], $existItem);
                } else {
                    $item = $existItem;
                }
                unset($existItems[$item['questionId']]);
            }

            if ($item['parentId'] == 0 && !in_array($item['questionType'], $types)) {
                $types[] = $item['questionType'];
            }
        }

        foreach ($existItems as $existItem) {
            $this->getTestpaperItemDao()->deleteItem($existItem['id']);
        }

        $metas = empty($testpaper['metas']) ? array() : $testpaper['metas'];
        $metas['question_type_seq'] = $types;

        $this->getTestpaperDao()->updateTestpaper($testpaper['id'], array(
            'itemCount' => $seq -1,
            'score' => $totalScore,
            'metas' => $metas,
        ));


    }

    public function canTeacherCheck($id)
    {
        $paper = $this->getTestpaperDao()->getTestpaper($id);
        if (!$paper) {
            throw $this->createServiceException('试卷不存在');
        }

        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return $user['id'];
        }

        $target = explode('-', $paper['target']);

        if ($target[0] == 'course') {
            $targetId = explode('/', $target[1]);
            $member = $this->getCourseService()->getCourseMember($targetId[0], $user['id']);

            // @todo: 这个是有问题的。
            if ($member['role'] == 'teacher') {
                return $user['id'];
            }
        }
        return false;
    }

    public function findTeacherTestpapersByTeacherId ($teacherId)
    {
        $members = $this->getMemberDao()->findAllMemberByUserIdAndRole($teacherId, 'teacher');

        $targets = array_map(function($member){
            return "course-".$member['courseId'];
        }, $members);

        return $this->getTestpaperDao()->findTestpaperByTargets($targets);
    }




	private function getTestpaperDao()
    {
        return $this->createDao('Testpaper.TestpaperDao');
    }

    private function getTestpaperResultDao()
    {
        return $this->createDao('Testpaper.TestpaperResultDao');
    }

    private function getTestpaperItemDao(){
        return $this->createDao('Testpaper.TestpaperItemDao');
    }

    private function getTestpaperItemResultDao(){
        return $this->createDao('Testpaper.TestpaperItemResultDao');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }

    private function getMemberDao ()
    {
        return $this->createDao('Course.CourseMemberDao');
    }

    private function getStatusService()
    {
        return $this->createService('User.StatusService');
    }

}