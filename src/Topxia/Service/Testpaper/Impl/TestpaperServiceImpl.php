<?php
namespace Topxia\Service\Testpaper\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Testpaper\TestpaperService;
use Topxia\Common\ArrayToolkit;

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

	public function searchTestpapers($conditions, $sort, $start, $limit)
	{
        return $this->getTestpaperDao()->searchTestpapers($conditions, $sort, $start, $limit);
    }

    public function searchTestpapersCount($conditions)
    {
        return $this->getTestpaperDao()->searchTestpapersCount($conditions);
	}

    public function publishTestpaper($id)
    {

    }

    public function closeTestpaper($id)
    {

    }

    public function deleteTestpaper($id)
    {

    }

    public function deleteTestpaperByIds(array $ids)
    {

    }

    public function buildTestpaper($id, $builder, $builderOptions)
    {

    }

    public function rebuildTestpaper($id, $builder, $builderOptions)
    {

    }



    public function findTestpaperResultsByTestpaperIdAndUserId($testpaperId, $userId)
    {
    	return $this->getTestpaperResultDao()->findTestpaperResultsByTestpaperIdAndUserId($testpaperId, $userId);
    }

    public function findTestPaperResultsByTestIdAndStatusAndUserId($testpaperId, $userId, array $status)
    {
    	return $this->getTestpaperResultDao()->findTestPaperResultsByTestIdAndStatusAndUserId($testpaperId, $status, $userId);
    }








    public function startTestpaper($id, $target)
    {
    	$testPaper = $this->getTestpaperDao()->getTestpaper($id);

    	$testPaperResult = array(
            'paperName' => $testPaper['name'],
            'testId' => $id,
            'userId' => $this->getCurrentUser()->id,
            'limitedTime' => $testPaper['limitedTime'],
            'beginTime' => time(),
            'status' => 'doing',
            'usedTime' => 0,
            'targetType' => empty($target['type']) ? '' : $target['type'],
            'targetId' => empty($target['id']) ? 0 : intval($target['id']),
        );

        return $this->getTestpaperResultDao()->addTestpaperResult($testPaperResult);
    }

    public function previewTestpaper($testpaperId)
    {
        $items = $this->getTestpaperItems($testpaperId);
        $items = ArrayToolkit::index($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));
        $questions = ArrayToolkit::index($questions, 'id');

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
        $itemResults = $this->getTestpaperItemResultDao()->findTestResultsByTestPaperResultId($testpaperResultId);
        $itemResults = ArrayToolkit::index($itemResults, 'questionId');

        $testpaperResult = $this->getTestpaperResultDao()->getTestpaperResult($testpaperResultId);

        $items = $this->getTestpaperItems($testpaperResult['testId']);
        $items = ArrayToolkit::index($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));
        $questions = ArrayToolkit::index($questions, 'id');

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
            'wrong' => 0,
            'noAnswer' => 0,
            'all' => 0,
            'score' => 0,
            'totalScore' => 0
        );
        $accuracy = array(
            'single_choice' => $accuracyResult,
            'choice' => $accuracyResult,
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

        $testpaperResult = $this->getTestPaperResultDao()->getTestpaperResult($id);

        if ($testpaperResult['userId'] != $userId) {
            throw $this->createAccessDeniedException('无权修改其他学员的试卷！');
        }

        if (in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
            throw $this->createServiceException("已经交卷的试卷不能更改答案!");
        }

        $items = $this->getTestpaperItems($testpaperResult['testId']);
        $items = ArrayToolkit::index($items, 'questionId');

        //得到当前用户答案
        $answers = $this->getTestpaperItemResultDao()->findTestResultsByTestPaperResultId($testpaperResult['id']);
        $answers = ArrayToolkit::index($answers, 'questionId');

        $answers = $this->formatAnswers($answers, $items);

        $answers = $this->getQuestionService()->judgeQuestions($answers, true);

        $answers = $this->makeScores($answers, $items);

        //记分
        $this->getTestpaperItemResultDao()->updateItemResults($answers, $testpaperResult['id']);

        return $this->getTestpaperItemResultDao()->findTestResultsByTestPaperResultId($testpaperResult['id']);
    }

    private function formatAnswers($answers, $items)
    {
        $results = array();
        foreach ($items as $item) {
            if (!array_key_exists($item['questionId'], $answers)){
                $results[$item['questionId']] = 'noAnswer';
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
                $answers[$questionId]['score'] = $items[$questionId]['score'] * $answer['percentage'] / 100;
            } else {
                $answers[$questionId]['score'] = 0;
            }
        }
        return $answers;
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

        $testpaperResult = $this->getTestPaperResultDao()->getTestpaperResult($id);

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

    private function filterTestAnswers ($testPaperResultId, $answers)
    {
        return $this->getTestpaperItemResultDao()->findTestResultsByItemIdAndTestId(array_keys($answers), $testPaperResultId);
    }

    public function reviewTestpaper($resultId, $items, $remark = null)
    {

    }

    public function updateTestpaperResult($id, $usedTime)
    {
        $testPaperResult = $this->getTestPaperResultDao()->getTestpaperResult($id);

        $fields['usedTime'] = $usedTime + $testPaperResult['usedTime'];

        $fields['updateTime'] = time();

        $fields['endTime'] = time();
        $fields['active'] = 1;

        $this->getTestpaperResultDao()->updateTestpaperResultActive($testPaperResult['testId'],$testPaperResult['userId']);

        return $this->getTestpaperResultDao()->updateTestpaperResult($id, $fields);
    }




    public function getTestpaperItems($testpaperId)
    {
        return $this->getTestItemDao()->findItemsByTestPaperId($testpaperId);
    }

    public function addItem($testpaperId, $questionId, $afterItemId = null)
    {

    }

    public function replaceItem($testpaperId, $itemId, $questionId)
    {

    }


    public function canTeacherCheck($id)
    {
        $paper = $this->getTestPaperDao()->getTestPaper($id);
        if (!$paper) {
            throw $this->createServiceException('试卷不存在');
        }

        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return $user['id'];
        }

        $target = explode('-', $paper['target']);

        if ($target[0] == 'course') {
            $targetId = explode('/', $targetType[1]);
            $course = $this->getCourseService()->getCourse($targetId[0]);

            // @todo: 这个是有问题的。
            if (in_array($user['id'], $course['teacherIds'])) {
                return $user['id'];
            }
        }
        return false;
    }





	private function getTestpaperDao()
    {
        return $this->createDao('Testpaper.TestpaperDao');
    }

    private function getTestpaperResultDao()
    {
        return $this->createDao('Testpaper.TestpaperResultDao');
    }

    private function getTestItemDao(){
        return $this->createDao('Quiz.TestItemDao');
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
}