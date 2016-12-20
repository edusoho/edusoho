<?php
namespace Biz\Testpaper\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Biz\Testpaper\Service\TestpaperService;
use Topxia\Common\Exception\ResourceNotFoundException;

class TestpaperServiceImpl extends BaseService implements TestpaperService
{
    public function getTestpaper($id)
    {
        return $this->getTestpaperDao()->get($id);
    }

    public function createTestpaper($fields)
    {
        $user = $this->getCurrentUser();

        $fields['createdUserId'] = $user['id'];
        $fields['createdTime']   = time();
        $fields['updatedUserId'] = $user['id'];
        $fields['updatedTime']   = time();

        return $this->getTestpaperDao()->create($fields);
    }

    public function updateTestpaper($id, $fields)
    {
        $testpaper = $this->getTestpaper($id);

        if (!$testpaper) {
            throw $this->createServiceException("Testpaper #{$id} is not found, update testpaper failure.");
        }

        $argument = $fields;

        $testpaperBuilder = $this->getTestpaperBuilder($testpaper['type']);
        $fields           = $testpaperBuilder->filterFields($fields);

        $testpaper = $this->getTestpaperDao()->update($id, $fields);

        $this->dispatchEvent('testpaper.update', array('argument' => $argument, 'testpaper' => $testpaper));

        return $testpaper;
    }

    public function deleteTestpaper($id)
    {
        $testpaper = $this->getTestpaper($id);
        if (!$testpaper) {
            throw $this->createServiceException("Testpaper #{$id} is not found, update testpaper failure.");
        }

        $result = $this->getTestpaperDao()->delete($testpaper['id']);
        $this->deleteItemsByTestId($testpaper['id']);

        $this->dispatchEvent('testpaper.delete', $testpaper);

        return $result;
    }

    public function deleteTestpapers($ids)
    {
        if (empty($ids)) {
            return false;
        }

        foreach ($ids as $id) {
            $this->deleteTestpaper($id);
        }

        return true;
    }

    public function findTestpapersByIds($ids)
    {
        $testpapers = $this->getTestpaperDao()->findTestpapersByIds($ids);
        return ArrayToolkit::index($testpapers, 'id');
    }

    public function findTestpapersByCopyIdAndLockedTarget($copyId, $lockedTarget)
    {
        return $this->getTestpaperDao()->findTestpapersByCopyIdAndLockedTarget($copyId, $lockedTarget);
    }

    public function searchTestpapers($conditions, $orderBy, $start, $limit)
    {
        return $this->getTestpaperDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchTestpaperCount($conditions)
    {
        return $this->getTestpaperDao()->count($conditions);
    }

    /**
     * testpaper_item
     */

    public function getItem($id)
    {
        return $this->getItemDao()->get($id);
    }

    public function createItem($fields)
    {
        return $this->getItemDao()->create($fields);
    }

    public function updateItem($id, $fields)
    {
        return $this->getItemDao()->update($id, $fields);
    }

    public function deleteItem($id)
    {
        return $this->getItemDao()->delete($id);
    }

    public function deleteItemsByTestId($testpaperId)
    {
        return $this->getItemDao()->deleteItemsByTestpaperId($testpaperId);
    }

    public function getItemsCountByParams(array $conditions, $groupBy = '')
    {
        return $this->getItemDao()->getItemsCountByParams($conditions, $groupBy);
    }

    public function findItemsByTestId($testpaperId)
    {
        $items = $this->getItemDao()->findItemsByTestId($testpaperId);
        return ArrayToolkit::index($items, 'questionId');
    }

    public function searchItems($conditions, $orderBy, $start, $limit)
    {
        return $this->getItemDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchItemCount($conditions)
    {
        return $this->getItemDao()->count($conditions);
    }

    /**
     * testpaper_item_result
     */

    public function createItemResult($fields)
    {
        return $this->getItemResultDao()->create($fields);
    }

    public function updateItemResult($itemResultId, $fields)
    {
        return $this->getItemResultDao()->update($itemResultId, $fields);
    }

    public function findItemResultsByResultId($resultId)
    {
        return $this->getItemResultDao()->findItemResultsByResultId($resultId);
    }

    /**
     * testpaper_result
     */

    public function getTestpaperResult($id)
    {
        return $this->getTestpaperResultDao()->get($id);
    }

    public function getUserUnfinishResult($testId, $courseId, $lessonId, $type, $userId)
    {
        return $this->getTestpaperResultDao()->getUserUnfinishResult($testId, $courseId, $lessonId, $type, $userId);
    }

    public function getUserLatelyResultByTestId($userId, $testId, $courseId, $lessonId, $type)
    {
        return $this->getTestpaperResultDao()->getUserLatelyResultByTestId($userId, $testId, $courseId, $lessonId, $type);
    }

    public function findPaperResultsStatusNumGroupByStatus($testId)
    {
        $numInfo = $this->getTestpaperResultDao()->findPaperResultsStatusNumGroupByStatus($testId);
        if (!$numInfo) {
            return array();
        }

        $statusInfo = array();
        foreach ($numInfo as $info) {
            $statusInfo[$info['status']] = $info['num'];
        }

        return $statusInfo;
    }

    public function addTestpaperResult($fields)
    {
        return $this->getTestpaperResultDao()->create($fields);
    }

    public function updateTestpaperResult($id, $fields)
    {
        $fields['updateTime'] = time();

        return $this->getTestpaperResultDao()->update($id, $fields);
    }

    public function searchTestpaperResultsCount($conditions)
    {
        return $this->getTestpaperResultDao()->count($conditions);
    }

    public function searchTestpaperResults($conditions, $sort, $start, $limit)
    {
        return $this->getTestpaperResultDao()->search($conditions, $sort, $start, $limit);
    }

    public function searchTestpapersScore($conditions)
    {
        return $this->getTestpaperResultDao()->searchTestpapersScore($conditions);
    }

    public function buildTestpaper($fields, $type)
    {
        $testpaperBuilder = $this->getTestpaperBuilder($type);

        return $testpaperBuilder->build($fields);
    }

    public function finishTest($resultId, $formData)
    {
        $user = $this->getCurrentUser();

        $result = $this->getTestpaperResult($resultId);

        if ($result['userId'] != $user['id']) {
            throw $this->createAccessDeniedException($this->getKernel()->trans('无权修改其他学员的试卷！'));
        }

        if (in_array($result['status'], array('reviewing', 'finished'))) {
            throw $this->createServiceException($this->getKernel()->trans('已经交卷的试卷不能修改!'));
        }

        $answers = empty($formData['data']) ? array() : $formData['data'];

        $this->submitAnswers($result['id'], $answers);

        $paperResult = $this->getTestpaperBuilder($result['type'])->updateSubmitedResult($result['id'], $formData['usedTime']);

        $this->dispatchEvent('testpaper.finish', new ServiceEvent($paperResult));

        return $paperResult;
    }

    public function publishTestpaper($id)
    {
        $testpaper = $this->getTestpaper($id);

        if (empty($testpaper)) {
            throw new ResourceNotFoundException('testpaper', $id);
        }

        if (!in_array($testpaper['status'], array('closed', 'draft'))) {
            throw $this->createServiceException($this->getKernel()->trans('试卷状态不合法!'));
        }

        $testpaper = $this->getTestpaperDao()->update($id, array('status' => 'open'));

        $this->dispatchEvent('testpaper.publish', new ServiceEvent($testpaper));

        return $testpaper;
    }

    public function closeTestpaper($id)
    {
        $testpaper = $this->getTestpaper($id);

        if (empty($testpaper)) {
            throw new ResourceNotFoundException('testpaper', $id);
        }

        if (!in_array($testpaper['status'], array('open'))) {
            throw $this->createAccessDeniedException($this->getKernel()->trans('试卷状态不合法!'));
        }

        $testpaper = $this->getTestpaperDao()->update($id, array('status' => 'closed'));

        $this->dispatchEvent('testpaper.close', new ServiceEvent($testpaper));

        return $testpaper;
    }

    public function countQuestionTypes($testpaper, $items)
    {
        $total = array();

        foreach ($testpaper['metas']['counts'] as $type => $count) {
            $total[$type]['score']     = empty($items[$type]) ? 0 : array_sum(ArrayToolkit::column($items[$type], 'score'));
            $total[$type]['number']    = empty($items[$type]) ? 0 : count($items[$type]);
            $total[$type]['missScore'] = empty($items[$type]) ? 0 : array_sum(ArrayToolkit::column($items[$type], 'missScore'));
        }

        return $total;
    }

    public function canBuildTestpaper($type, $options)
    {
        $builder = $this->getTestpaperBuilder($type);
        return $builder->canBuild($options);
    }

    public function startTestpaper($id, $lessonId)
    {
        $testpaper = $this->getTestpaper($id);
        $user      = $this->getCurrentuser();

        $testpaperResult = $this->getUserUnfinishResult($testpaper['id'], $testpaper['courseId'], $lessonId, $testpaper['type'], $user['id']);

        if (!$testpaperResult) {
            $fields = array(
                'paperName'   => $testpaper['name'],
                'testId'      => $id,
                'userId'      => $user['id'],
                'limitedTime' => $testpaper['limitedTime'],
                'beginTime'   => time(),
                'status'      => 'doing',
                'usedTime'    => 0,
                'courseId'    => $testpaper['courseId'],
                'courseSetId' => $testpaper['courseSetId'],
                'lessonId'    => $lessonId,
                'type'        => $testpaper['type']
            );

            $testpaperResult = $this->addTestpaperResult($fields);
        }

        return $testpaperResult;
    }

    protected function completeQuestion($items, $questions)
    {
        foreach ($items as $item) {
            if (!in_array($item['questionId'], ArrayToolkit::column($questions, 'id'))) {
                $questions[$item['questionId']] = array(
                    'id'        => $item['questionId'],
                    'isDeleted' => true,
                    'stem'      => $this->getKernel()->trans('此题已删除'),
                    'score'     => 0,
                    'answer'    => '',
                    'type'      => $item['questionType']
                );
            }
        }

        return $questions;
    }

    public function showTestpaperItems($testId, $resultId = 0)
    {
        $testpaper        = $this->getTestpaper($testId);
        $testpaperBuilder = $this->getTestpaperBuilder($testpaper['type']);

        return $testpaperBuilder->showTestItems($testId, $resultId);
    }

    public function makeAccuracy($resultId)
    {
        $testpaperResult = $this->getTestpaperResult($resultId);
        $items           = $this->findItemsByTestId($testpaperResult['testId']);

        $itemResults = $this->findItemResultsByResultId($resultId);
        $itemResults = ArrayToolkit::index($itemResults, 'questionId');

        $accuracy = array();

        foreach ($items as $item) {
            $itemResult = empty($itemResults[$item['questionId']]) ? array() : $itemResults[$item['questionId']];

            if ($item['parentId'] > 0 || $item['questionType'] == 'material') {
                $accuracy['material'] = empty($accuracy['material']) ? array() : $accuracy['material'];

                $accuracy['material'] = $this->countItemResultStatus($accuracy['material'], $item, $itemResult);

                $accuracy['material'] = $accuracyResult;
            } else {
                $accuracy[$item['questionType']] = empty($accuracy[$item['questionType']]) ? array() : $accuracy[$item['questionType']];

                $accuracyResult = $this->countItemResultStatus($accuracy[$item['questionType']], $item, $itemResult);

                $accuracy[$item['questionType']] = $accuracyResult;
            }
        }

        return $accuracy;
    }

    //new
    public function checkFinish($resultId, $fields)
    {
        $paperResult = $this->getTestpaperResult($resultId);

        $user = $this->getCurrentuser();

        $checkData = $fields['result'];
        unset($fields['result']);

        $items = $this->findItemsByTestId($paperResult['testId']);

        $userAnswers = $this->findItemResultsByResultId($paperResult['id']);
        $userAnswers = ArrayToolkit::index($userAnswers, 'questionId');

        foreach ($items as $questionId => $item) {
            $checkedFields = empty($checkData[$questionId]) ? array() : $checkData[$questionId];
            if (!$checkedFields) {
                continue;
            }

            $userAnswer = empty($userAnswers[$questionId]) ? array() : $userAnswers[$questionId];
            if (!$userAnswer) {
                continue;
            }

            if (!empty($userAnswer['answer'])) {
                $checkedFields['status'] = $checkedFields['score'] == $item['score'] ? 'right' : 'wrong';
            }

            $this->updateItemResult($userAnswer['id'], $checkedFields);
        }

        $fields['checkTeacherId']  = $user['id'];
        $fields['checkedTime']     = time();
        $fields['subjectiveScore'] = array_sum(ArrayToolkit::column($checkData, 'score'));
        $fields['score']           = $paperResult['objectiveScore'] + $fields['subjectiveScore'];
        $fields['status']          = 'finished';

        $paperResult = $this->updateTestpaperResult($paperResult['id'], $fields);

        $this->dispatchEvent('testpaper.reviewed', new ServiceEvent($paperResult));

        return $paperResult;
    }

    //new
    public function submitAnswers($id, $answers)
    {
        if (empty($answers)) {
            return array();
        }

        $user            = $this->getCurrentUser();
        $testpaperResult = $this->getTestpaperResult($id);
        $questionIds     = array_keys($answers);

        $paperItems = $this->findItemsByTestId($testpaperResult['testId']);

        $itemResults = $this->findItemResultsByResultId($testpaperResult['id']);
        $itemResults = ArrayToolkit::index($itemResults, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $this->getItemResultDao()->db()->beginTransaction();

        try {
            foreach ($answers as $questionId => $answer) {
                $fields = array('answer' => $answer);

                $question  = empty($questions[$questionId]) ? array() : $questions[$questionId];
                $paperItem = empty($paperItems[$questionId]) ? array() : $paperItems[$questionId];

                if (!$question) {
                    $fields['status'] = 'notFound';
                    $fields['score']  = 0;
                } else {
                    $question['score']     = empty($paperItem['score']) ? 0 : $paperItem['score'];
                    $question['missScore'] = empty($paperItem['missScore']) ? 0 : $paperItem['missScore'];

                    $answerStatus     = $this->getQuestionService()->judgeQuestion($question, $answer);
                    $fields['status'] = $answerStatus['status'];
                    $fields['score']  = $answerStatus['score'];
                }

                if (!empty($itemResults[$questionId])) {
                    $this->updateItemResult($itemResults[$questionId]['id'], $fields);
                } else {
                    $fields['testId']     = $testpaperResult['testId'];
                    $fields['resultId']   = $testpaperResult['id'];
                    $fields['userId']     = $user['id'];
                    $fields['questionId'] = $questionId;
                    $fields['answer']     = $answer;

                    $this->createItemResult($fields);
                }
            }
            $this->getItemResultDao()->db()->commit();
        } catch (\Exception $e) {
            $this->getItemResultDao()->db()->rollback();
            throw $e;
        }

        return $this->findItemResultsByResultId($testpaperResult['id']);
    }

    public function sumScore($itemResults)
    {
        $score          = 0;
        $rightItemCount = 0;

        foreach ($itemResults as $itemResult) {
            $score += $itemResult['score'];

            if ($itemResult['status'] == 'right') {
                $rightItemCount++;
            }
        }

        return array(
            'sumScore'       => $score,
            'rightItemCount' => $rightItemCount
        );
    }

    public function updateTestpaperItems($testpaperId, $fields)
    {
        $newItems = $fields['questions'];
        $newItems = ArrayToolkit::index($newItems, 'id');

        if (!$newItems) {
            return false;
        }

        $testpaper = $this->getTestpaper($testpaperId);
        $argument  = $fields;

        if (empty($testpaperId)) {
            throw $this->createServiceException();
        }

        $existItems  = $this->findItemsByTestId($testpaperId);
        $questionIds = array_keys($newItems);
        $questions   = $this->getQuestionService()->findQuestionsByIds($questionIds);

        try {
            $this->beginTransaction();

            $this->deleteItemsByTestId($testpaper['id']);
            $this->createItems($newItems, $questions, $testpaper['id']);

            $testpaper = $this->updateTestpaperByItems($testpaper['id'], $fields);
            $this->commit();

            return $testpaper;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function createItems($newItems, $questions, $testpaperId)
    {
        if (!$questions) {
            return array();
        }

        $index = 1;
        foreach ($newItems as $questionId => $item) {
            $question = !empty($questions[$questionId]) ? $questions[$questionId] : array();
            if (!$question) {
                continue;
            }

            $filter['seq']          = $index++;
            $filter['questionId']   = $question['id'];
            $filter['questionType'] = $question['type'];
            $filter['testId']       = $testpaperId;
            $filter['score']        = $item['score'];
            $filter['missScore']    = empty($item['missScore']) ? 0 : $item['missScore'];
            $filter['parentId']     = $question['parentId'];
            $items[]                = $this->createItem($filter);
        }

        return $items;
    }

    protected function updateTestpaperByItems($testpaperId, $fields)
    {
        $testpaper = $this->getTestpaper($testpaperId);

        $items      = $this->findItemsByTestId($testpaperId);
        $conditions = array(
            'testId'          => $testpaperId,
            'parentIdDefault' => 0
        );
        $fields['itemCount'] = $this->searchItemCount($conditions);
        $fields['metas']     = $testpaper['metas'];

        $totalScore = 0;
        if ($items) {
            $type = array();
            foreach ($items as $item) {
                if ($item['questionType'] != 'material') {
                    $totalScore += $item['score'];
                }

                if (!in_array($item['questionType'], $type) && $item['parentId'] != 0) {
                    $type[] = $item['questionType'];
                }
            }
            $fields['metas']['question_type_seq'] = $type;
        }

        $fields['score'] = $totalScore;

        $testpaper = $this->updateTestpaper($testpaperId, $fields);

        return $testpaper;
    }

    //new
    protected function countItemResultStatus($resultStatus, $item, $questionResult)
    {
        $resultStatus = array(
            'score'      => empty($resultStatus['score']) ? 0 : $resultStatus['score'],
            'totalScore' => empty($resultStatus['totalScore']) ? 0 : $resultStatus['totalScore'],
            'all'        => empty($resultStatus['all']) ? 0 : $resultStatus['all'],
            'right'      => empty($resultStatus['right']) ? 0 : $resultStatus['right'],
            'partRight'  => empty($resultStatus['partRight']) ? 0 : $resultStatus['partRight'],
            'wrong'      => empty($resultStatus['wrong']) ? 0 : $resultStatus['wrong'],
            'noAnswer'   => empty($resultStatus['noAnswer']) ? 0 : $resultStatus['noAnswer']
        );

        $score  = empty($questionResult['score']) ? 0 : $questionResult['score'];
        $status = empty($questionResult['status']) ? 'noAnswer' : $questionResult['status'];
        $resultStatus['score'] += $score;
        $resultStatus['totalScore'] += $item['score'];

        $resultStatus['all']++;

        if ($status == 'right') {
            $resultStatus['right']++;
        }

        if ($status == 'partRight') {
            $resultStatus['partRight']++;
        }

        if ($status == 'wrong') {
            $resultStatus['wrong']++;
        }

        if ($status == 'noAnswer') {
            $resultStatus['noAnswer']++;
        }

        return $resultStatus;
    }

    public function findAttachments($testId)
    {
        $items       = $this->findItemsByTestId($testId);
        $questionIds = ArrayToolkit::column($items, 'questionId');

        return $this->getQuestionService()->findAttachments($questionIds);
    }

    public function canLookTestpaper($resultId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        $paperResult = $this->getTestpaperResult($resultId);

        if (!$paperResult) {
            throw $this->createNotFoundException($this->getKernel()->trans('试卷结果不存在!'));
        }

        $paper = $this->getTestpaper($paperResult['testId']);

        if (!$paper) {
            throw $this->createNotFoundException($this->getKernel()->trans('试卷不存在!'));
        }

        if ($paperResult['status'] == 'doing' && ($paperResult['userId'] != $user['id'])) {
            throw $this->createNotFoundException('无权查看此试卷');
        }

        if ($user->isAdmin()) {
            return true;
        }
        //dosomething

        return true;
    }

    public function getTestpaperBuilder($type)
    {
        return $this->biz["testpaper_builder.{$type}"];
    }

    public function getTestpaperPattern($pattern)
    {
        return $this->biz["testpaper_pattern.{$pattern}"];
    }

    protected function getTestpaperDao()
    {
        return $this->createDao('Testpaper:TestpaperDao');
    }

    protected function getTestpaperResultDao()
    {
        return $this->createDao('Testpaper:TestpaperResultDao');
    }

    protected function getItemDao()
    {
        return $this->createDao('Testpaper:TestpaperItemDao');
    }

    protected function getItemResultDao()
    {
        return $this->createDao('Testpaper:TestpaperItemResultDao');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getKernel()->createService('File:UploadFileService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
