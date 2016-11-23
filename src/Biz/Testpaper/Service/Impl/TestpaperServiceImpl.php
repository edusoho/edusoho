<?php
namespace Biz\Testpaper\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\Builder\TestpaperBuilderFactory;
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

        if (empty($testpaper)) {
            throw $this->createServiceException("Testpaper #{$id} is not found, update testpaper failure.");
        }

        $argument = $fields;

        $testpaperBuilder = $this->getTestpaperBuilder($testpaper['type']);
        $fields           = $testpaperBuilder->filterFields($fields);

        $testpaper = $this->getTestpaperDao()->update($id, $fields);

        $this->dispatchEvent("testpaper.update", array('argument' => $argument, 'testpaper' => $testpaper));

        return $testpaper;
    }

    public function deleteTestpaper($id)
    {
        $testpaper = $this->getTestpaper($id);
        $result    = $this->getTestpaperDao()->delete($id);
        $this->deleteTestpaperItemByTestId($id);

        $this->dispatchEvent('testpaper.delete', $testpaper);

        return $result;
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

    public function submitTestpaper($resultId, $answers)
    {
        $result           = $this->getTestpaperResult($resultId);
        $testpaperBuilder = $this->getTestpaperBuilder($result['type']);

        return $testpaperBuilder->submit($fields);
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

        $testpaper = array(
            'status' => 'open'
        );
        $testpaper = $this->updateTestpaper($id, array('status' => 'open'));

        $this->dispatchEvent('testpaper.publish', $testpaper);

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

        $testpaper = $this->updateTestpaper($id, array('status' => 'closed'));

        $this->dispatchEvent('testpaper.close', $testpaper);

        return $testpaper;
    }

    public function deleteTestpaperItemByTestId($testpaperId)
    {
        return $this->getItemDao()->deleteItemsByTestpaperId($testpaperId);
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
                    'isDeleted' => true,
                    'stem'      => $this->getKernel()->trans('此题已删除'),
                    'score'     => 0,
                    'answer'    => ''
                );
            }
        }

        return $questions;
    }

    public function previewTestpaper($testpaperId)
    {
        $items     = $this->findItemsByTestId($testpaperId);
        $items     = ArrayToolkit::index($items, 'questionId');
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

                $items[$item['parentId']]['items'][$questionId]                    = $items[$questionId];
                $formatItems['material'][$item['parentId']]['items'][$item['seq']] = $items[$questionId];
                unset($items[$questionId]);
            } else {
                $formatItems[$item['questionType']][$item['questionId']] = $items[$questionId];
            }
        }

        ksort($formatItems);
        return $formatItems;
        // 'questionIds' => $items = ArrayToolkit::column($items, 'questionId')
    }

    public function showTestpaperItems($resultId)
    {
        $result           = $this->getTestpaperResult($resultId);
        $testpaperBuilder = $this->getTestpaperBuilder($result['type']);

        return $testpaperBuilder->showTestItems($resultId);
    }

    public function makeAccuracy($resultId)
    {
        $testpaperResult = $this->getTestpaperResult($resultId);
        $items           = $this->findItemsByTestId($testpaperResult['testId']);

        $itemResults = $this->findItemResultsByResultId($resultId);
        $itemResults = ArrayToolkit::index($itemResults, 'questionId');

        $accuracy = array();

        foreach ($itemResults as $itemResult) {
            $item = $items[$itemResult['questionId']];

            if ($item['parentId'] > 0) {
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

    public function makeTeacherFinishTest($id, $paperId, $teacherId, $field)
    {
        $testResults = array();

        $teacherSay   = $field['teacherSay'];
        $passedStatus = $field['passedStatus'];
        unset($field['teacherSay']);
        unset($field['passedStatus']);

        $items = $this->getItemDao()->findItemsByTestpaperId($paperId);
        $items = ArrayToolkit::index($items, 'questionId');

        $userAnswers = $this->findItemResultsByResultId($id);
        $userAnswers = ArrayToolkit::index($userAnswers, 'questionId');

        foreach ($field as $key => $value) {
            $keys = explode('_', $key);

            if (!is_numeric($keys[1])) {
                throw $this->createServiceException($this->getKernel()->trans('得分必须为数字！'));
            }

            $testResults[$keys[1]][$keys[0]] = $value;
            $userAnswer                      = $userAnswers[$keys[1]]['answer'];

            if ($keys[0] == 'score') {
                if ($value == $items[$keys[1]]['score']) {
                    $testResults[$keys[1]]['status'] = 'right';
                } elseif ($userAnswer[0] == '') {
                    $testResults[$keys[1]]['status'] = 'noAnswer';
                } else {
                    $testResults[$keys[1]]['status'] = 'wrong';
                }
            }
        }

        //是否要加入教师阅卷的锁
        $this->getItemResultDao()->updateItemEssays($testResults, $id);

        $this->getQuestionService()->statQuestionTimes($testResults);

        $testpaperResult = $this->getTestpaperResult($id);

        $subjectiveScore = array_sum(ArrayToolkit::column($testResults, 'score'));

        $totalScore = $subjectiveScore + $testpaperResult['objectiveScore'];

        $testPaperResult = $this->updateTestpaperResult($id, array(
            'score'           => $totalScore,
            'subjectiveScore' => $subjectiveScore,
            'status'          => 'finished',
            'checkTeacherId'  => $teacherId,
            'checkedTime'     => time(),
            'teacherSay'      => $teacherSay,
            'passedStatus'    => $passedStatus
        ));

        $testpaper = $this->getTestpaper($testpaperResult['testId']);
        $this->dispatchEvent(
            'testpaper.reviewed',
            new ServiceEvent($testpaper, array('testpaperResult' => $testpaperResult))
        );

        return $testPaperResult;
    }

    //new
    protected function submitAnswers($id, $answers)
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

    public function updateTestpaperItems($testpaperId, $items)
    {
        $testpaper = $this->getTestpaper($testpaperId);
        $argument  = $items;

        if (empty($testpaperId)) {
            throw $this->createServiceException();
        }

        $existItems  = $this->findItemsByTestId($testpaperId);
        $questionIds = ArrayToolkit::column($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        if (count($items) != count($questions)) {
            throw $this->createServiceException($this->getKernel()->trans('数据缺失'));
        }

        $types      = array();
        $totalScore = 0;
        $seq        = 1;
        $items      = ArrayToolkit::index($items, 'questionId');

        /*foreach ($items as $questionId => $item) {
        if ($questions[$questionId]['type'] == 'material') {
        $items[$questionId]['score'] = 0;
        }

        if ($questions[$questionId]['parentId'] > 0) {
        $items[$questions[$questionId]['parentId']]['score'] += $item['score'];
        }
        }*/

        foreach ($items as $item) {
            $question      = $questions[$item['questionId']];
            $item['seq']   = $seq;
            $item['score'] = $question['type'] == 'material' ? 0 : $item['score'];

            if ($question['subCount'] == 0) {
                $seq++;
                $totalScore += $item['score'];
            }

            if ($question['parentId'] > 0) {
                $items[$question['parentId']]['score'] += $item['score'];
            }

            if (empty($existItems[$item['questionId']])) {
                $item['questionType'] = $question['type'];
                $item['parentId']     = $question['parentId'];

// @todo, wellming.

                if (array_key_exists('missScore', $testpaper['metas']) && array_key_exists($question['type'], $testpaper['metas']['missScore'])) {
                    $item['missScore'] = $testpaper['metas']['missScore'][$question['type']];
                } else {
                    $item['missScore'] = 0;
                }

                $item['testId'] = $testpaperId;
                $item           = $this->createItem($item);
            } else {
                $existItem = $existItems[$item['questionId']];

                if ($item['seq'] != $existItem['seq'] || $item['score'] != $existItem['score']) {
                    $existItem['seq']   = $item['seq'];
                    $existItem['score'] = $item['score'];
                    $item               = $this->updateItem($existItem['id'], $existItem);
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
            $this->deleteItem($existItem['id']);
        }

        $metas                      = empty($testpaper['metas']) ? array() : $testpaper['metas'];
        $metas['question_type_seq'] = $types;

        $this->dispatchEvent("testpaper.item.update", array('testpaper' => $testpaper, 'argument' => $argument));

        $testpaper = $this->updateTestpaper($testpaper['id'], array(
            'itemCount' => $seq - 1,
            'score'     => $totalScore,
            'metas'     => $metas
        ));
    }

    public function canTeacherCheck($id)
    {
        $paper = $this->getTestpaper($id);

        if (!$paper) {
            throw $this->createServiceException($this->getKernel()->trans('试卷不存在'));
        }

        $user = $this->getCurrentUser();

        if ($user->isSuperAdmin()) {
            return $user['id'];
        }

        $target = explode('-', $paper['target']);

        if ($target[0] == 'course') {
            $targetId = explode('/', $target[1]);
            $member   = $this->getCourseService()->getCourseMember($targetId[0], $user['id']);

// @todo: 这个是有问题的。

            if ($member['role'] == 'teacher') {
                return $user['id'];
            }

            $classroom = $this->getClassroomService()->findClassroomByCourseId($targetId[0]);

            if (!empty($classroom)) {
                $isTeacher              = $this->getClassroomService()->isClassroomTeacher($classroom['classroomId'], $user['id']);
                $isAssistant            = $this->getClassroomService()->isClassroomAssistant($classroom['classroomId'], $user['id']);
                $isClassroomHeadTeacher = $this->getClassroomService()->isClassroomHeadTeacher($classroom['classroomId'], $user['id']);

                if ($isTeacher || $isAssistant || $isClassroomHeadTeacher) {
                    return $user['id'];
                }
            }
        }

        return false;
    }

    public function canLookTestpaper($resultId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        $paperResult = $this->getTestpaperResult($resultId);

        if (!$paperResult) {
            throw $this->createNotFoundException($this->getKernel()->trans('试卷不存在!'));
        }

        $paper = $this->getTestpaper($paperResult['testId']);

        if (!$paper) {
            throw $this->createNotFoundException($this->getKernel()->trans('试卷不存在!'));
        }

        if (($paperResult['status'] == 'doing' || $paper['status'] == 'paused') && ($paperResult['userId'] != $user['id'])) {
            throw $this->createNotFoundException('无权查看此试卷');
        }

        if ($user->isAdmin()) {
            return $user['id'];
        }

        $member = $this->getCourseService()->getCourseMember($paper['courseId'], $user['id']);

        if ($member['role'] == 'teacher') {
            return $user['id'];
        }

        if ($paperResult['userId'] == $user['id']) {
            return $user['id'];
        }

        $classroom = $this->getClassroomService()->findClassroomByCourseId($paper['courseId']);

        if (!empty($classroom)) {
            $isTeacher              = $this->getClassroomService()->isClassroomTeacher($classroom['classroomId'], $user['id']);
            $isAssistant            = $this->getClassroomService()->isClassroomAssistant($classroom['classroomId'], $user['id']);
            $isClassroomHeadTeacher = $this->getClassroomService()->isClassroomHeadTeacher($classroom['classroomId'], $user['id']);

            if ($isTeacher || $isAssistant || $isClassroomHeadTeacher) {
                return $user['id'];
            }
        }

        return false;
    }

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
        $resultStatus['score'] += $questionResult['score'];
        $resultStatus['totalScore'] += $item['score'];

        $resultStatus['all']++;

        if ($questionResult['status'] == 'right') {
            $resultStatus['right']++;
        }

        if ($questionResult['status'] == 'partRight') {
            $resultStatus['partRight']++;
        }

        if ($questionResult['status'] == 'wrong') {
            $resultStatus['wrong']++;
        }

        if ($questionResult['status'] == 'noAnswer') {
            $resultStatus['noAnswer']++;
        }

        return $resultStatus;
    }

    public function getTestpaperBuilder($type)
    {
        return TestpaperBuilderFactory::create($this->biz, $type);
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
        return $this->getKernel()->createService('Course.CourseService');
    }

    protected function getMemberDao()
    {
        return $this->getKernel()->createDao('Course.CourseMemberDao');
    }

    protected function getStatusService()
    {
        return $this->getKernel()->createService('User.StatusService');
    }

    protected function getClassroomService()
    {
        return $this->getKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
