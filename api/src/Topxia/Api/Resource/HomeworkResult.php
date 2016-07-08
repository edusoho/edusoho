<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class HomeworkResult extends BaseResource
{
    public function post(Application $app, Request $request, $homeworkId)
    {
        $answers = $request->request->all();
        $answers = !empty($answers['data']) ? $answers['data'] : array();
        $result = $this->getHomeworkService()->startHomework($homeworkId);
        $this->getHomeworkService()->submitHomework($result['id'], $answers);
        $res = array(
            'id' => $result['id'],
        );

        return $res;
    }

    public function get(Application $app, Request $request, $lessonId)
    {
        $user = $this->getCurrentUser();
        $homework = $this->getHomeworkService()->getHomeworkByLessonId($lessonId);
        if (empty($homework)) {
            return '';
        }
        $homeworkResults = $this->getHomeworkService()->searchResults(
            array('homeworkId' => $homework['id'], 'userId' => $user['id']), array('createdTime', 'DESC'), 0, 1);
        if (empty($homeworkResults)) {
            return '';
        }
        $homeworkResult = $homeworkResults[0];
        $canLookHomeworkResult = $this->getHomeworkService()->canLookHomeworkResult($homeworkResult['id']);
        if (!$canLookHomeworkResult) {
            throw $this->createAccessDeniedException('无权查看作业！');
        }
        $itemSetResults = $this->getHomeworkService()->findItemResultsbyHomeworkResultId($homeworkResult['id']);
        $homeworkResult['items'] = $this->filterItem($itemSetResults);

        return $this->filter($homeworkResult);
    }

    private function filterItem($items)
    {
        $questionIds = ArrayToolkit::column($items, 'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $materialMap = array();
        $itemIndexMap = array();
        $newItems = array();
        foreach ($items as &$item) {
            unset($item['answer']);
            unset($item['userId']);

            $question = $questions[$item['questionId']];
            $item['questionType'] = $question['type'];
            $item['questionParentId'] = $question['parentId'];

            if ('material' == $item['questionType']) {
                $itemIndexMap[$item['questionId']] = $item['id'];
                $materialMap[$item['questionId']] = array();
            }

            if ($item['questionParentId'] != 0 && isset($materialMap[$item['questionParentId']])) {
                $materialMap[$item['questionParentId']][] = $item;
                continue;
            }

            $newItems[$item['id']] = $item;
        }

        foreach ($materialMap as $id => $material) {
            $newItems[$itemIndexMap[$id]]['items'] = $material;
        }

        return array_values($newItems);
    }

    public function filter($res)
    {
        $res['usedTime'] = date('c', $res['usedTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);
        $res['createdTime'] = date('c', $res['createdTime']);

        return $res;
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }
}
